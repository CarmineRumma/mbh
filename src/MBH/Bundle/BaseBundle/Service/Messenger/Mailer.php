<?php

namespace MBH\Bundle\BaseBundle\Service\Messenger;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Mailer service
 */
class Mailer implements \SplObserver
{
    /**
     * @var array
     */
    private $params;

    /**
     * @var \Swift_Mailer
     */
    private $mailer;

    /**
     * @var \Twig_Environment
     */
    private $twig;

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container;

    /**
     * @var \Doctrine\ODM\MongoDB\DocumentManager
     */
    private $dm;

    /**
     * @var string
     */
    private $locale;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->twig = $container->get('twig');
        $this->mailer = $container->get('mailer');
        $this->params = $container->getParameter('mbh.mailer');
        $this->dm = $this->container->get('doctrine_mongodb');
        $this->locale = $this->container->getParameter('locale');
    }

    /**
     * @param string $local
     * @return $this
     */
    public function setLocal($local)
    {
        $this->locale = $local;
        return $this;
    }

    /**
     * @param \SplSubject $notifier
     */
    public function update(\SplSubject $notifier)
    {
        /** @var NotifierMessage $message */
        $message = $notifier->getMessage();

        if ($message->getEmail()) {
            $this->send($message->getRecipients(), array_merge([
                'text' => $message->getText(),
                'type' => $message->getType(),
                'category' => $message->getCategory(),
                'subject' => $message->getSubject(),
                'hotel' => $message->getHotel(),
                'link' => $message->getLink(),
                'linkText' => $message->getLinkText(),
                'order' => $message->getOrder(),
                'signature' => $message->getSignature(),
                'transParams' => $message->getTranslateParams()
            ], $message->getAdditionalData()), $message->getTemplate());
        }
    }

    /**
     * @param $data
     * @param \Swift_Message $message
     * @param $template
     * @return mixed
     */
    public function addImages($data, \Swift_Message $message, $template)
    {
        $baseUrl = str_replace('app_dev.php/', '', $this->container->get('router')->generate('_welcome', [], true));
        $crawler = new Crawler($this->twig->render($template, $data));
        foreach ($crawler->filterXpath('//img') as $domElement) {
            $id = $domElement->getAttribute('data-name');
            $src = $domElement->getAttribute('src');

            strpos($src, 'http') === false ? $http = $baseUrl : $http = '';

            if (!empty($id) && !empty($src)) {
                $data[$id] = $message->embed(
                    \Swift_Image::fromPath($http . $src));
            }
        }

        return $data;
    }

    /**
     * @param RecipientInterface[] $recipients
     * @param array $data
     * @param null $template
     * @return bool
     * @throws \Exception
     */
    public function send(array $recipients, array $data, $template = null)
    {
        if (empty($recipients)) {

            $error = 'Не удалось отправить письмо. Нет ни одного получателя.';

            if (empty($data['category'])) {
                throw new \Exception($error);
            }

            $recipients = $this->dm->getRepository('MBHUserBundle:User')->findBy(
                [$data['category'] . 's' => true, 'enabled' => true, 'locked' => false]
            );

            if (!count($recipients)) {
                throw new \Exception($error);
            }
        }
        (empty($data['subject'])) ? $data['subject'] = $this->params['subject'] : $data['subject'];
        $message = \Swift_Message::newInstance();
        empty($template) ? $template = $this->params['template'] : $template;

        $data['hotelName'] = 'MaxiBooking';
        $data = $this->addImages($data, $message, $template);
        $translator = $this->container->get('translator');

        foreach ($recipients as $recipient) {
            //@todo move to notifier
            $transParams = [
                '%guest%' => $recipient->getName(),
                '%hotel%' => null
            ];

            if ($data['hotel']) {
                $data['hotelName'] = $data['hotel']->getName();
                $transParams['%hotel%'] = $data['hotel']->getName();
            }

            if ($recipient->getCommunicationLanguage() && $recipient->getCommunicationLanguage() != $this->locale) {
                $translator->setLocale($recipient->getCommunicationLanguage());
                $data['isSomeLanguage'] = false;
                if ($data['hotel'] && $data['hotel']->getInternationalTitle()) {
                    $data['hotelName'] = $data['hotel']->getInternationalTitle();
                    $transParams['%hotel%'] = $data['hotel']->getInternationalTitle();
                }
            } else {
                $translator->setLocale($this->locale);
                $data['isSomeLanguage'] = true;
            }

            $data['transParams'] = array_merge($transParams, $data['transParams']);
            $body = $this->twig->render($template, $data);

            $fromText = empty($data['fromText']) ?
                (empty($data['hotelName']) ? $this->params['fromText'] : $data['hotelName']) :
                $data['fromText'];

            $data['hotelName'] = $data['hotelName'] ?: 'MaxiBooking';

            $message
                ->setSubject($translator->trans($data['subject'], $data['transParams']))
                ->setFrom([$this->params['fromMail'] => $fromText])
                ->setBody($body, 'text/html');
            $message->setTo([$recipient->getEmail() => $recipient->getName()]);
            $this->mailer->send($message);
        }

        if (php_sapi_name() == 'cli') {
            $spool = $this->mailer->getTransport()->getSpool();
            $transport = $this->container->get('swiftmailer.transport.real');
            $spool->flushQueue($transport);
        }

        return true;
    }
}

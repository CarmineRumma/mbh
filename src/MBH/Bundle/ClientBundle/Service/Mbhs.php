<?php

namespace MBH\Bundle\ClientBundle\Service;

use Guzzle\Http\Message\Response;
use MBH\Bundle\OnlineBundle\Document\Order;
use MBH\Bundle\PackageBundle\Document\Tourist;
use MBH\Bundle\PackageBundle\Document\UnwelcomeItem;
use Symfony\Component\DependencyInjection\ContainerInterface;
use MBH\Bundle\BaseBundle\Document\Message;
use MBH\Bundle\PackageBundle\Document\Package;

class Mbhs
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    /**
     * @var \Doctrine\Bundle\MongoDBBundle\ManagerRegistry
     */
    protected $dm;

    /**
     * @var \Guzzle\Service\Client
     */
    protected $guzzle;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var \Symfony\Component\HttpFoundation\Request
     */
    protected $request;

    protected $checkIp = true;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->dm = $container->get('doctrine_mongodb')->getManager();
        $this->guzzle = $container->get('guzzle.client');
        $this->config = $container->getParameter('mbh.mbhs');
        $this->request = $container->get('request');

        if (in_array($this->request->getClientIp(), ['95.85.3.188'])) {
            $this->checkIp = false;
        }
    }

    /**
     * @param string $text
     * @param string $phone
     * @return \stdClass
     */
    public function sendSms($text, $phone)
    {
        $result = new \stdClass();
        $result->error = false;

        if (!$this->checkIp) {
            $result->error = true;
            return $result;
        }

        try {
            $request = $this->guzzle->get(base64_decode($this->config['mbhs']) . 'client/sms/send');
            $request->getQuery()->set('url', $this->getSchemeAndHttpHost());
            $request->getQuery()->set('key', $this->config['key']);
            $request->getQuery()->set('sms', $text);
            $request->getQuery()->set('phone', $phone);

            $response = $request->send();
            $json = $response->json();

        } catch (\Exception $e) {
            $result->error = true;
            $result->message = $e->getMessage();
            $result->code = $e->getCode();

            $this->sendMessage('sms', 'Sms не отправлено. Ошибка: ' . $result->message . ' (' . $result->code . ')');
            return $result;
        }

        if (!$json || $json['error']) {
            $result->error = true;
            $result->message = $json['message'];
            $result->code = $json['code'];

            $this->sendMessage('sms', 'Sms не отправлено. Ошибка: ' . $result->message . ' (' . $result->code . ')');
            return $result;
        };

        return $result;
    }

    /**
     * @param string $from
     * @param string $text
     * @param string $type
     */
    protected function sendMessage($from, $text, $type = 'danger')
    {
        $message = new Message();
        $end = new \DateTime();
        $end->modify('+30 seconds');

        $message->setFrom($from)->setText($text)->setType($type)->setEnd($end);
        $this->dm->persist($message);
        $this->dm->flush();
    }

    /**
     * @param $ip
     * @return boolean
     */
    public function login($ip)
    {
        if (!$this->checkIp) {
            return false;
        }

        try {
            $request = $this->guzzle->get(base64_decode($this->config['mbhs']) . 'client/login');
            $request->getQuery()->set('url', $this->getSchemeAndHttpHost());
            $request->getQuery()->set('key', $this->config['key']);
            $request->getQuery()->set('ip', $ip);

            $request->send();

        } catch (\Exception $e) {
            if ($this->container->get('kernel')->getEnvironment() == 'dev') {
                dump($e);
            };
            return false;
        }

        return true;
    }

    /**
     * @param Package $package
     * @param $ip
     * @return bool
     */
    public function sendPackageInfo(Package $package, $ip)
    {
        $ip = $this->getIp($ip);
        if (!$this->checkIp) {
            return false;
        }

        try {
            $request = $this->guzzle
                ->post(base64_decode($this->config['mbhs']) . 'client/package/log')
                ->setBody(json_encode(array_merge($package->toArray(), [
                    'url' => $this->getSchemeAndHttpHost(),
                    'key' => $this->config['key'],
                    'ip' => $ip
                ])))
                ->setHeader('Content-Type', 'application/json')
                ->send()
            ;

        } catch (\Exception $e) {
            if ($this->container->get('kernel')->getEnvironment() == 'dev') {
                dump($e->getMessage());
                dump($e);
            };
            return false;
        }

        return $request;
    }

    /**
     * @param $ip
     * @return string
     */
    public function getIp($ip)
    {
        if (php_sapi_name() == 'cli' && !$ip) {
            $host = gethostname();
            $ip = gethostbyname($host);
        }

        return $ip;
    }

    /**
     * @return string
     */
    public function getSchemeAndHttpHost()
    {
        if (php_sapi_name() == 'cli') {
            $result = $this->container->getParameter('router.request_context.scheme') . '://';
            $result .= $this->container->getParameter('router.request_context.host');
        } else {
            $result = $this->request->getSchemeAndHttpHost();
        }

        return $result;
    }

    /**
     * @param UnwelcomeItem $blackListInfo
     * @return bool
     */
    public function addUnwelcomeItem(UnwelcomeItem $blackListInfo)
    {
        try {
            /** @var Response $response */
            $response = $this->guzzle
                ->post(base64_decode($this->config['mbhs']) . 'client/unwelcome/add')
                ->setBody(json_encode([
                    'comment' => $blackListInfo->getComment(),
                    'isAggressor' => $blackListInfo->getIsAggressor(),
                    'tourist' => $blackListInfo->getTourist()->jsonSerialize(),
                    'url' => $this->getSchemeAndHttpHost(),
                    'key' => $this->config['key'],
                ]))
                ->setHeader('Content-Type', 'application/json')
                ->send()
            ;
            return true;
        } catch (\Exception $e) {
            if ($this->container->get('kernel')->getEnvironment() == 'dev') {
                dump($e->getMessage());
                dump($e);
            };
            return false;
        }
    }

    /**
     * @param UnwelcomeItem $blackListInfo
     * @return bool
     */
    public function updateUnwelcomeItem(UnwelcomeItem $blackListInfo)
    {
        try {
            /** @var Response $response */
            $response = $this->guzzle
                ->post(base64_decode($this->config['mbhs']) . 'client/unwelcome/update')
                ->setBody(json_encode([
                    'comment' => $blackListInfo->getComment(),
                    'isAggressor' => $blackListInfo->getIsAggressor(),
                    'tourist' => $blackListInfo->getTourist()->jsonSerialize(),
                    'url' => $this->getSchemeAndHttpHost(),
                    'key' => $this->config['key'],
                ]))
                ->setHeader('Content-Type', 'application/json')
                ->send()
            ;
            return true;
        } catch (\Exception $e) {
            if ($this->container->get('kernel')->getEnvironment() == 'dev') {
                dump($e->getMessage());
                dump($e);
            };
            return false;
        }
    }

    /**
     * @param Tourist $tourist
     * @return null|array
     */
    public function findUnwelcomeItemByTourist(Tourist $tourist)
    {
        try {
            /** @var Response $response */
            $response = $this->guzzle
                ->post(base64_decode($this->config['mbhs']) . 'client/unwelcome/find_by_tourist')
                ->setBody(json_encode([
                    'tourist' => $tourist->jsonSerialize(),
                    'url' => $this->getSchemeAndHttpHost(),
                    'key' => $this->config['key'],
                ]))
                ->setHeader('Content-Type', 'application/json')
                ->send()
            ;
            return json_decode($response->getBody(true), true);
        } catch (\Exception $e) {
            if ($this->container->get('kernel')->getEnvironment() == 'dev') {
                dump($e->getMessage());
                dump($e);
            };
            return null;
        }
    }

    /**
     * @param Tourist $tourist
     * @return array|null
     */
    public function deleteUnwelcomeItemByTourist(Tourist $tourist)
    {
        try {
            /** @var Response $response */
            $response = $this->guzzle
                ->post(base64_decode($this->config['mbhs']) . 'client/unwelcome/delete_by_tourist')
                ->setBody(json_encode([
                    'tourist' => $tourist->jsonSerialize(),
                    'url' => $this->getSchemeAndHttpHost(),
                    'key' => $this->config['key'],
                ]))
                ->setHeader('Content-Type', 'application/json')
                ->send()
            ;
            return json_decode($response->getBody(true), true);
        } catch (\Exception $e) {
            if ($this->container->get('kernel')->getEnvironment() == 'dev') {
                dump($e->getMessage());
                dump($e);
            };
            return null;
        }
    }
}
<?php

namespace MBH\Bundle\PackageBundle\DocumentGenerator\Xls;


use MBH\Bundle\PackageBundle\DocumentGenerator\GeneratorFactoryInterface;
use MBH\Bundle\PackageBundle\DocumentGenerator\Xls\Type\NoticeStayPlaceXlsType;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class DocumentXlsGeneratorFactory
 *
 * @author Aleksandr Arofikin <sasaharo@gmail.com>
 */
class XlsGeneratorFactory implements GeneratorFactoryInterface
{
    const TYPE_NOTICE = 'xls_notice';

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @return array
     */
    public function getAvailableTypes()
    {
        return [
            self::TYPE_NOTICE
        ];
    }

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * @param $type
     * @return \Symfony\Component\Form\Form|null
     */
    public function createFormByType($type)
    {
        if ($type == self::TYPE_NOTICE) {
            return $this->container->get('form.factory')->create(new NoticeStayPlaceXlsType());
        }

        return null;
    }

    /**
     * @param $type
     * @return bool
     */
    public function hasForm($type)
    {
        return $type == self::TYPE_NOTICE;
    }


    public function createGeneratorByType($type)
    {
        if($type == self::TYPE_NOTICE) {
            $generator = new NoticeStayPlaceXlsGenerator();
            $generator->setContainer($this->container);
            return $generator;
        }
        throw new \InvalidArgumentException();
    }
}
<?php

namespace MBH\Bundle\VegaBundle\Service;


use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\PackageBundle\Document\Order;
use MBH\Bundle\PackageBundle\Document\OrderDocument;
use MBH\Bundle\PackageBundle\Document\Package;
use MBH\Bundle\PackageBundle\Document\Tourist;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Exception\ValidatorException;
use Symfony\Component\Validator\ValidatorBuilder;

/**
 * Class VegaExport
 * @package MBH\Bundle\VegaBundle\Services
 *

 */
class VegaExport
{
    /**
     * @var Container
     */
    private $container;
    /**
     * @var array
     */
    private $documentTypes;
    /**
     * @var array
     */
    private $scanTypes;

    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->documentTypes = $this->container->get('mbh.vega.dictionary_provider')->getDocumentTypes();
        $this->scanTypes = $this->container->get('mbh.vega.dictionary_provider')->getScanTypes();
    }

    /**
     * @param Tourist $tourist
     * @return bool
     */
    private function isValid(Tourist $tourist)
    {
        /** @var ValidatorBuilder $validatorBuilder */
        $validatorBuilder = $this->container->get('validator.builder');
        $validatorBuilder->disableAnnotationMapping()->addYamlMapping(__DIR__ . '/../Resources/config/additional_validation.yml');
        $validator = $validatorBuilder->getValidator();
        /** @var ConstraintViolationListInterface $constraintViolationList */
        $constraintViolationList = $validator->validate($tourist);

        return $constraintViolationList->count() == 0;
    }

    /**
     * @todo try catch \Twig_Error
     * @todo throw ValidationException
     * @param Tourist $tourist
     * @param Package $package
     * @param array|OrderDocument[] $touristDocuments
     * @return string
     */
    private function getXmlString(Tourist $tourist, Package $package, Hotel $hotel, array $touristDocuments = [])
    {
        //@todo $this->isValid($tourist);

        $xml = $this->container->get('twig')->render('MBHVegaBundle::vega_export.xml.twig', [
            'tourist' => $tourist,
            'package' => $package,
            'documents' => $touristDocuments,
            'documentTypes' => $this->documentTypes,
            'scanTypes' => $this->scanTypes,
            'hotel' => $hotel
        ]);

        return $xml;
    }

    /**
     * @param Order $order
     * @param Tourist $tourist
     * @return OrderDocument[]
     */
    public function getDocumentsByOrderAndTourist(Order $order, Tourist $tourist)
    {
        $orderDocuments = [];
        $documents = $order->getDocuments();
        $vegaTypes = array_keys($this->container->get('mbh.vega.dictionary_provider')->getDocumentTypes());
        foreach ($documents as $document) {
            if ($document->getTourist() && $document->getTourist()->getId() == $tourist->getId() && in_array($document->getType(), $vegaTypes)) {
                $orderDocuments[] = $document;
            }
        }

        return $orderDocuments;
    }


    /**
     * @param Package[] $packages
     * @param Hotel $hotel
     */
    private function getXmlStringListWithFiles(array $packages, Hotel $hotel)
    {
        $xmlList = [];
        $fileName = 'FrOrg' . date('Ymdhis');
        foreach ($packages as $package) {
            $tourists = $package->getTourists();
            $tourists[] = $package->getMainTourist();
            foreach ($tourists as $k => $tourist) {
                $touristDocuments = $this->getDocumentsByOrderAndTourist($package->getOrder(), $tourist);
                $xml = $this->getXmlString($tourist, $package, $hotel, $touristDocuments);
                $files = array_map(function ($document) {
                    return $document->getFile();
                }, $touristDocuments);
                $xmlList[$fileName . ($k > 0 ? '_(' . $k . ')' : '') . '.xml'] = ['xml' => $xml, 'files' => $files];
            }
        }

        return $xmlList;
    }

    /**
     * @param Package[] $packages
     * @param Hotel $hotel
     * @return \SplFileInfo|void
     */
    public function exportToZip(array $packages, Hotel $hotel)
    {
        $xmlList = $this->getXmlStringListWithFiles($packages, $hotel);

        if (!$xmlList) {
            return;
        }

        $uploadedPath = $this->container->get('kernel')->getRootDir() . '/../protectedUpload/vegaDocuments';

        $zip = new \ZipArchive();
        $zipName = 'FrOrg' . date('Ymdhis') . '.zip';
        $fullZipName = $uploadedPath . DIRECTORY_SEPARATOR . $zipName;

        if (is_file($fullZipName)) {
            unlink($fullZipName);
        }

        if ($zip->open($fullZipName, \ZipArchive::CREATE)) {
            foreach ($xmlList as $fileName => $data) {
                $zip->addFromString($fileName, $data['xml']);
                /** @var UploadedFile $file */
                foreach ($data['files'] as $file) {
                    $fileName = $file->getRealPath();
                    $localName = 'images' . DIRECTORY_SEPARATOR . $file->getClientOriginalName();
                    $zip->addFile($fileName, $localName);
                }
            }
            $zip->close();

            return new \SplFileInfo($fullZipName);
        }
    }
}
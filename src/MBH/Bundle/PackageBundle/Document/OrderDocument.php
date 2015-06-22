<?php

namespace MBH\Bundle\PackageBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Gedmo\Blameable\Traits\BlameableDocument;
use Gedmo\Timestampable\Traits\TimestampableDocument;
use MBH\Bundle\PackageBundle\Lib\PayerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ODM\EmbeddedDocument
 * @ODM\HasLifecycleCallbacks
 *
 * @author Aleksandr Arofikin <sashaaro@gmail.com>
 */
class OrderDocument
{
    use TimestampableDocument;
    use BlameableDocument;

    /**
     * @var string
     * @ODM\String
     * @Assert\NotNull(message="validator.document.OrderDocument.type")
     */
    protected $type;

    /**
     * @var string
     * @ODM\String
     */
    protected $scanType;

    /**
     * @var string
     * @ODM\String
     */
    protected $name;

    /**
     * @var string
     * @ODM\String
     */
    protected $originalName;

    /**
     * @var string
     * @ODM\String
     */
    protected $comment;

    /**
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\PackageBundle\Document\Tourist")
     */
    protected $tourist;

    /**
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\PackageBundle\Document\Organization")
     */
    protected $organization;

    /**
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\CashBundle\Document\CashDocument")
     */
    protected $cashDocument;

    /**
     * @var UploadedFile
     * @Assert\File(maxSize="6M", mimeTypes={
     *          "image/png",
     *          "image/jpeg",
     *          "image/jpg",
     *          "image/gif",
     *          "application/pdf",
     *          "application/x-pdf",
     *          "application/msword",
     *          "application/xls",
     *          "application/xlsx",
     *          "application/vnd.ms-excel"
     * }, mimeTypesMessage="validator.document.OrderDocument.file_type")
     */
    protected $file;

    /**
     * Client Original Extension
     * @var string
     * @ODM\String
     */
    protected $extension;


    /**
     * @var string
     * @ODM\String
     */
    protected $mimeType;

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }


    /**
     * @return string
     */
    public function getScanType()
    {
        return $this->scanType;
    }

    /**
     * @param string $scanType
     */
    public function setScanType($scanType)
    {
        $this->scanType = $scanType;
    }

    /**
     * @return UploadedFile|null
     */
    public function getFile()
    {
        if (!$this->file && $this->name && is_file($this->getPath())) {
            $this->file = new UploadedFile($this->getPath(), $this->getName());
        }

        return $this->file;
    }

    /**
     * @param UploadedFile $file
     */
    public function setFile(UploadedFile $file = null)
    {
        $this->file = $file;
        if ($this->file && $this->file->isFile()) {
            $this->originalName = $this->file->getClientOriginalName();
            $this->name = uniqid().'.'.$this->file->getClientOriginalExtension();
            $this->extension = $this->file->getClientOriginalExtension();
            $this->mimeType = $this->file->getMimeType();
        }
    }

    /**
     * @return bool
     */
    public function isImage()
    {
        return in_array($this->extension, ['jpg', 'png', 'jpeg']);
    }

    /**
     * The absolute directory path where uploaded
     * documents should be saved
     * @return string
     */
    public function getUploadRootDir()
    {
        return __DIR__.'/../../../../../protectedUpload/orderDocuments';
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->getUploadRootDir().DIRECTORY_SEPARATOR.$this->getName();
    }

    public function upload()
    {
        if (null === $this->getFile()) {
            return;
        }

        $this->getFile()->move($this->getUploadRootDir(), $this->getName());
    }

    /**
     * @return bool
     */
    public function isUploaded()
    {
        return is_file($this->getPath());
    }

    /**
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @param string $comment
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return bool
     */
    public function deleteFile()
    {
        if ($this->getFile() && is_writable($this->getFile()->getPathname())) {
            $result = unlink($this->getFile()->getPathname());
            if ($result) {
                $this->file = null;
            }

            return $result;
        }

        return false;
    }

    /**
     * @return string
     */
    public function getOriginalName()
    {
        return $this->originalName;
    }

    /**
     * @param string $originalName
     */
    public function setOriginalName($originalName)
    {
        $this->originalName = $originalName;
    }

    /**
     * @return null|Tourist
     */
    public function getTourist()
    {
        return $this->tourist;
    }

    /**
     * @param Tourist $tourist
     */
    public function setTourist(Tourist $tourist = null)
    {
        $this->tourist = $tourist;
    }

    /**
     * @return null|Organization
     */
    public function getOrganization()
    {
        return $this->organization;
    }

    /**
     * @param null|Organization $organization
     */
    public function setOrganization(Organization $organization = null)
    {
        $this->organization = $organization;
    }

    /**
     * @return string
     */
    public function getExtension()
    {
        return $this->extension;
    }

    /**
     * Client Original Extension
     * @param string $extension
     */
    public function setExtension($extension)
    {
        $this->extension = $extension;
    }

    /**
     * @return string
     */
    public function getMimeType()
    {
        return $this->mimeType;
    }

    /**
     * @param string $mimeType
     */
    public function setMimeType($mimeType)
    {
        $this->mimeType = $mimeType;
    }

    /**
     * @see Organization
     * @see Tourist
     *
     * @return PayerInterface|null
     */
    public function getPayer()
    {
        if ($this->getOrganization()) {
            return $this->getOrganization();
        } elseif ($this->getTourist()) {
            return $this->getTourist();
        }

        return null;
    }

    /**
     * @return \MBH\Bundle\CashBundle\Document\CashDocument
     */
    public function getCashDocument()
    {
        return $this->cashDocument;
    }

    /**
     * @param mixed $cashDocument
    /* */
    public function setCashDocument(\MBH\Bundle\CashBundle\Document\CashDocument $cashDocument = null)
    {
        $this->cashDocument = $cashDocument;
    }
}
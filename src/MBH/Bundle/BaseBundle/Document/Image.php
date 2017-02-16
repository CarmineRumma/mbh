<?php

namespace MBH\Bundle\BaseBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Gedmo\Timestampable\Traits\TimestampableDocument;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\HttpFoundation\File\File;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ODM\Document(collection="Images")
 * @Vich\Uploadable
 * @Gedmo\Loggable()
 */
class Image
{
    use TimestampableDocument;

    /** @ODM\Id */
    protected $id;

    /**
     * @var File
     * @Vich\UploadableField(mapping="upload_image", fileNameProperty="imageName")
     * @Assert\File(maxSize = "1024k", maxSizeMessage="validator.image.max_size_exceeded")
     */
    protected $imageFile;

    /**
     * @var string
     * @ODM\Field(type="string")
     */
    protected $imageName;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\Field(type="string")
     */
    protected $height;

    /**
     * @var bool
     * @ODM\Field(type="bool")
     */
    protected $isDefault = false;

    /**
     * @Gedmo\Versioned
     * @ODM\Field(type="string", name="width")
     */
    public $width;

    public function setImageFile(File $image = null)
    {
        $this->imageFile = $image;

        if ($image) {
            list($width, $height) = getimagesize($image);
            $this->setWidth($width);
            $this->setHeight($height);

            $this->updatedAt = new \DateTimeImmutable();
        }

        return $this;
    }

    public function getImageFile()
    {
        return $this->imageFile;
    }

    /**
     * @param string $imageName
     *
     * @return Image
     */
    public function setImageName($imageName)
    {
        $this->imageName = $imageName;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getImageName()
    {
        return $this->imageName;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     * @return Image
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return Image
     */
    public function setDescription(string $description): Image
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return string
     */
    public function getHeight(): ?string
    {
        return $this->height;
    }

    /**
     * @param string $height
     * @return Image
     */
    public function setHeight(string $height): Image
    {
        $this->height = $height;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * @param mixed $width
     * @return Image
     */
    public function setWidth($width)
    {
        $this->width = $width;

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsDefault(): ?bool
    {
        return $this->isDefault;
    }

    /**
     * @param bool $isDefault
     * @return Image
     */
    public function setIsDefault(bool $isDefault): Image
    {
        $this->isDefault = $isDefault;

        return $this;
    }


//    public function uploadImage(UploadedFile $uploadedImage)
//    {
//        if (is_null($uploadedImage)) {
//            return;
//        }
//        $this->setName($uploadedImage->getClientOriginalName());
//        $uploadedImage->move($this->getUploadRootDir(), $uploadedImage->getClientOriginalName());
//        $this->setFile(self::HOTEL_UPLOAD_DIR.'/'. $uploadedImage->getClientOriginalName());
//    }
}
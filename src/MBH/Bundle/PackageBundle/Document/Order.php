<?php

namespace MBH\Bundle\PackageBundle\Document;

use MBH\Bundle\BaseBundle\Document\Base;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use MBH\Bundle\PackageBundle\Lib\PayerInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableDocument;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableDocument;
use Gedmo\Blameable\Traits\BlameableDocument;

/**
 * @ODM\Document(collection="Order", repositoryClass="MBH\Bundle\PackageBundle\Document\OrderRepository")
 * @Gedmo\Loggable
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 * @ODM\HasLifecycleCallbacks
 */
class Order extends Base
{
    /**
     * Hook timestampable behavior
     * updates createdAt, updatedAt fields
     */
    use TimestampableDocument;

    /**
     * Hook softdeleteable behavior
     * deletedAt field
     */
    use SoftDeleteableDocument;
    
    /**
     * Hook blameable behavior
     * createdBy&updatedBy fields
     */
    use BlameableDocument;

    /**
     * @var int
     * @ODM\Id(strategy="INCREMENT")
     */
    protected $id;

    /**
     * @Gedmo\Versioned
     * @ODM\ReferenceOne(targetDocument="PackageSource")
     */
    protected $source;

    /**
     * @ODM\ReferenceMany(targetDocument="Package", mappedBy="order")
     */
    protected $packages;

    /**
     * @Gedmo\Versioned
     * @ODM\ReferenceOne(targetDocument="Tourist", inversedBy="orders")
     */
    protected $mainTourist;

    /**
     * @ODM\ReferenceOne(targetDocument="Organization")
     */
    protected $organization;

    /** @ODM\ReferenceMany(targetDocument="MBH\Bundle\CashBundle\Document\CashDocument", mappedBy="order") */
    protected $cashDocuments;

    /**
     * @var int
     * @Gedmo\Versioned
     * @ODM\Float()
     * @Assert\Type(type="numeric")
     * @Assert\Range(
     *      min=0,
     *      minMessage= "validator.document.order.price_min_message"
     * )
     */
    protected $price;

    /**
     * @var float
     * @Gedmo\Versioned
     * @ODM\Float()
     * @Assert\Type(type="numeric")
     * @Assert\Range(
     *      min=0,
     *      minMessage= "validator.document.package.price_less_zero"
     * )
     */
    protected $totalOverwrite;

    /**
     * @var int
     * @Gedmo\Versioned
     * @ODM\Float()
     * @Assert\Type(type="numeric")
     * @Assert\Range(
     *      min=0,
     *      minMessage= "validator.document.order.payed_sum_min_message"
     * )
     */
    protected $paid = 0;

    /**
     * @var boolean
     * @Gedmo\Versioned
     * @ODM\Boolean()
     * @Assert\Type(type="boolean")
     */
    protected $isPaid = false;

    /**
     * @var boolean
     * @Gedmo\Versioned
     * @ODM\Boolean()
     * @Assert\Type(type="boolean")
     */
    protected $confirmed = false;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\String(name="status")
     * @Assert\Choice(
     *      choices = {"offline", "online", "channel_manager"},
     *      message = "validator.document.order.wrong_status"
     * )
     */
    protected $status;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\String(name="channelManagerType")
     * @Assert\Choice(
     *      choices = {"vashotel", "booking"},
     *      message = "validator.document.package.wrong_channel_manager_type"
     * )
     */
    protected $channelManagerType;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\String(name="channelManagerId")
     */
    protected $channelManagerId;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\String()
     */
    protected $channelManagerHumanId;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\String()
     */
    protected $channelManagerStatus;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\String(name="note")
     */
    protected $note;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\String()
     */
    protected $card;

    /**
     * @var array
     * @ODM\EmbedMany(targetDocument="OrderDocument")
     */
    protected $documents = [];

    public function __construct()
    {
        $this->packages = new \Doctrine\Common\Collections\ArrayCollection();
        $this->documents = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add package
     *
     * @param \MBH\Bundle\PackageBundle\Document\Package $package
     */
    public function addPackage(\MBH\Bundle\PackageBundle\Document\Package $package)
    {
        $this->packages[] = $package;
    }

    /**
     * Remove package
     *
     * @param \MBH\Bundle\PackageBundle\Document\Package $package
     */
    public function removePackage(\MBH\Bundle\PackageBundle\Document\Package $package)
    {
        $this->packages->removeElement($package);
    }

    /**
     * Get packages
     *
     * @return \Doctrine\Common\Collections\Collection $packages
     */
    public function getPackages()
    {
        return $this->packages;
    }

    /**
     * Set mainTourist
     *
     * @param \MBH\Bundle\PackageBundle\Document\Tourist $mainTourist
     * @return self
     */
    public function setMainTourist(\MBH\Bundle\PackageBundle\Document\Tourist $mainTourist = null)
    {
        $this->mainTourist = $mainTourist;
        return $this;
    }

    /**
     * Get mainTourist
     *
     * @return \MBH\Bundle\PackageBundle\Document\Tourist|null $mainTourist
     */
    public function getMainTourist()
    {
        return $this->mainTourist;
    }

    /**
     * @return Organization
     */
    public function getOrganization()
    {
        return $this->organization;
    }

    /**
     * @param mixed $organization
     */
    public function setOrganization(Organization $organization = null)
    {
        $this->organization = $organization;
    }

    /**
     * Set price
     *
     * @param int $price
     * @return self
     */
    public function setPrice($price)
    {
        $this->price = $price;
        return $this;
    }

    /**
     * Get price
     * @param boolean $isFloat
     * @return float $price
     */
    public function getPrice($isFloat = false)
    {
        if (!empty($this->getTotalOverwrite())) {
            return $this->getTotalOverwrite();
        }

        if ($isFloat) {
            return number_format((float) $this->price, 2, '.', '');
        }
        return $this->price;
    }

    /**
     * Set paid
     *
     * @param int $paid
     * @return self
     */
    public function setPaid($paid)
    {
        $this->paid = $paid;
        return $this;
    }

    /**
     * Get paid
     *
     * @return int $paid
     */
    public function getPaid()
    {
        return $this->paid;
    }

    /**
     * Set isPaid
     *
     * @param boolean $isPaid
     * @return self
     */
    public function setIsPaid($isPaid)
    {
        $this->isPaid = $isPaid;
        return $this;
    }

    /**
     * Get isPaid
     *
     * @return boolean $isPaid
     */
    public function getIsPaid()
    {
        return $this->isPaid;
    }

    /**
     * Set confirmed
     *
     * @param boolean $confirmed
     * @return self
     */
    public function setConfirmed($confirmed)
    {
        $this->confirmed = $confirmed;
        return $this;
    }

    /**
     * Get confirmed
     *
     * @return boolean $confirmed
     */
    public function getConfirmed()
    {
        return $this->confirmed;
    }

    /**
     * Set status
     *
     * @param string $status
     * @return self
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * Get status
     *
     * @return string $status
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set note
     *
     * @param string $note
     * @return self
     */
    public function setNote($note)
    {
        $this->note = $note;
        return $this;
    }

    /**
     * Get note
     *
     * @return string $note
     */
    public function getNote()
    {
        return $this->note;
    }

    public function calcPrice(Package $excludePackage = null)
    {
        $this->price = 0;

        foreach ($this->getPackages() as $package) {
            if (empty($excludePackage) || $excludePackage->getId() != $package->getId()) {
                $this->price += $package->getPrice();
            }
        }
        return $this;
    }

    /**
     * Add cashDocument
     *
     * @param \MBH\Bundle\CashBundle\Document\CashDocument $cashDocument
     */
    public function addCashDocument(\MBH\Bundle\CashBundle\Document\CashDocument $cashDocument)
    {
        $this->cashDocuments[] = $cashDocument;
    }

    /**
     * Remove cashDocument
     *
     * @param \MBH\Bundle\CashBundle\Document\CashDocument $cashDocument
     */
    public function removeCashDocument(\MBH\Bundle\CashBundle\Document\CashDocument $cashDocument)
    {
        $this->cashDocuments->removeElement($cashDocument);
    }

    /**
     * Get cashDocuments
     *
     * @return \Doctrine\Common\Collections\Collection $cashDocuments
     */
    public function getCashDocuments()
    {
        return $this->cashDocuments;
    }

    /**
     * @ODM\PrePersist
     */
    public function prePersist()
    {
        $this->checkPaid();
    }

    /**
     * @ODM\preUpdate
     */
    public function preUpdate()
    {
        $this->checkPaid();
    }

    public function checkPaid()
    {
        if ($this->getPaid() >= $this->getPrice()) {
            $this->setIsPaid(true);
        } else {
            $this->setIsPaid(false);
        }
    }

    /**
     * Set source
     *
     * @param \MBH\Bundle\PackageBundle\Document\PackageSource $source
     * @return self
     */
    public function setSource(\MBH\Bundle\PackageBundle\Document\PackageSource $source = null)
    {
        $this->source = $source;
        return $this;
    }

    /**
     * Get source
     *
     * @return \MBH\Bundle\PackageBundle\Document\PackageSource $source
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * Set card
     *
     * @param string $card
     * @return self
     */
    public function setCard($card)
    {
        $this->card = $card;
        return $this;
    }

    /**
     * Get card
     *
     * @return string $card
     */
    public function getCard()
    {
        return $this->card;
    }

    /**
     * Set channelMangerHumanId
     *
     * @param string $channelManagerHumanId
     * @return self
     */
    public function setChannelManagerHumanId($channelManagerHumanId)
    {
        $this->channelManagerHumanId = $channelManagerHumanId;
        return $this;
    }

    /**
     * Get channelMangerHumanId
     *
     * @return string $channelMangerHumanId
     */
    public function getChannelManagerHumanId()
    {
        return $this->channelMangerHumanId;
    }

    /**
     * Set channelManagerType
     *
     * @param string $channelManagerType
     * @return self
     */
    public function setChannelManagerType($channelManagerType)
    {
        $this->channelManagerType = $channelManagerType;
        return $this;
    }



    /**
     * Get channelManagerType
     *
     * @return string $channelManagerType
     */
    public function getChannelManagerType()
    {
        return $this->channelManagerType;
    }

    /**
     * Set channelManagerId
     *
     * @param string $channelManagerId
     * @return self
     */
    public function setChannelManagerId($channelManagerId)
    {
        $this->channelManagerId = $channelManagerId;
        return $this;
    }

    /**
     * Get channelManagerId
     *
     * @return string $channelManagerId
     */
    public function getChannelManagerId()
    {
        return $this->channelManagerId;
    }

    /**
     * Set totalOverwrite
     *
     * @param float $totalOverwrite
     * @return self
     */
    public function setTotalOverwrite($totalOverwrite)
    {
        $this->totalOverwrite = $totalOverwrite;
        return $this;
    }

    /**
     * Get totalOverwrite
     *
     * @return float $totalOverwrite
     */
    public function getTotalOverwrite()
    {
        return $this->totalOverwrite;
    }

    public function removeAllPackages()
    {
        $this->packages = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set $channelManagerStatus
     *
     * @param string $channelManagerStatus
     * @return self
     */
    public function setChannelManagerStatus($channelManagerStatus)
    {
        $this->channelManagerStatus = $channelManagerStatus;
        return $this;
    }

    /**
     * Get channelMangerHumanId
     *
     * @return string $channelManagerStatus
     */
    public function getChannelManagerStatus()
    {
        return $this->channelManagerStatus;
    }

    /**
     * Add document
     *
     * @param \MBH\Bundle\PackageBundle\Document\OrderDocument $document
     */
    public function addDocument(\MBH\Bundle\PackageBundle\Document\OrderDocument $document)
    {
        $this->documents[] = $document;
    }

    /**
     * Remove document
     *
     * @param \MBH\Bundle\PackageBundle\Document\OrderDocument $document
     */
    public function removeDocument(\MBH\Bundle\PackageBundle\Document\OrderDocument $document)
    {
        $this->documents->removeElement($document);
    }

    /**
     * Get documents
     *
     * @return \Doctrine\Common\Collections\Collection|OrderDocument[] $documents
     */
    public function getDocuments()
    {
        return $this->documents;
    }

    /**
     * @param string $name
     * @return OrderDocument|null
     */
    public function getDocument($name)
    {
        foreach ($this->getDocuments() as $doc) {
            if ($doc->getName() == $name) {
                return $doc;
            }
        }

        return null;
    }

    /**
     * @param string $name
     * @return Order
     */
    public function removeDocumentByName($name)
    {
        $doc = $this->getDocument($name);

        if ($doc) {
            $this->removeDocument($doc);
        }

        return $this;
    }

    /**
     * @return null|string
     */
    public function getPaidStatus()
    {
        if ($this->getIsPaid()) {
            return 'success';
        }
        if ($this->getPaid() && !$this->getIsPaid()) {
            return 'warning';
        }
        if (!$this->getPaid()) {
            return 'danger';
        }

        return null;
    }

    /**
     * @return float
     */
    public function getDebt()
    {
        return $this->getPrice() - $this->getPaid();
    }

    /**
     * @return PayerInterface|null
     */
    public function getPayer()
    {
        if ($this->getOrganization()) {
            return $this->getOrganization();
        } elseif ($this->getMainTourist()) {
            return $this->getMainTourist();
        } else {
            null;
        }
    }

    /**
     * @return array
     */
    public function getFee()
    {
        $fee = [];
        foreach($this->getCashDocuments() as $doc) {
            if ($doc->getOperation() == 'fee') {
                $fee[] = $doc;
            }
        }

        return $fee;
    }
}
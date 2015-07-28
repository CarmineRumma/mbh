<?php

namespace MBH\Bundle\PackageBundle\Document;

use MBH\Bundle\BaseBundle\Document\Base;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use MBH\Bundle\PackageBundle\Lib\PayerInterface;
use MBH\Bundle\VegaBundle\Document\VegaState;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableDocument;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableDocument;
use Gedmo\Blameable\Traits\BlameableDocument;
use Zend\Stdlib\JsonSerializable;

/**
 * @ODM\Document(collection="Tourists", repositoryClass="MBH\Bundle\PackageBundle\Document\TouristRepository")
 * @Gedmo\Loggable
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 * @ODM\HasLifecycleCallbacks
 */
class Tourist extends Base implements JsonSerializable, PayerInterface
{
    public function jsonSerialize()
    {
        return [
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
            'patronymic' => $this->patronymic,
            'birthday' => $this->birthday ? $this->birthday->format('d.m.Y') : null,
            'phone' => $this->phone,
            'email' => $this->email,
        ];
    }

    /**
     * Cleans phone numbers
     * @param $phone
     * @return string
     */
    public static function cleanPhone($phone)
    {
        return preg_replace("/[^0-9]/", "", $phone);
    }

    /**
     * Returns formatted phone number
     * @param string $phone
     * @param boolean $original
     * @return string
     */
    public static function formatPhone($phone, $original = false)
    {
        $phone = self::cleanPhone($phone);

        if ($original || strlen($phone) < 7) {
            return $phone;
        } else {
            return empty($phone) ? null : '+ ' . substr($phone, 0, strlen($phone) - 7) . ' ' .
                substr($phone, -7, 3) . '-' .
                substr($phone, -4, 2) . '-' .
                substr($phone, -2, 2);
        }
    }

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
     * @ODM\ReferenceMany(targetDocument="Order", nullable="true", mappedBy="mainTourist")
     */
    public $orders;

    /**
     * @ODM\ReferenceMany(targetDocument="Package", nullable="true", mappedBy="tourists")
     */
    public $packages;

    /**
     * @ODM\ReferenceMany(targetDocument="MBH\Bundle\CashBundle\Document\CashDocument", mappedBy="payer")
     */
    protected $cashDocuments;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\String(name="firstName")
     * @Assert\NotNull(message= "validator.document.Tourist.name_required")
     * @Assert\Length(
     *      min=2,
     *      minMessage= "validator.document.Tourist.min_name",
     *      max=100,
     *      maxMessage= "validator.document.Tourist.max_name"
     * )
     */
    protected $firstName;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\String(name="lastName")
     * @Assert\NotNull(message= "validator.document.Tourist.surname_required")
     * @Assert\Length(
     *      min=2,
     *      minMessage= "validator.document.Tourist.min_surname",
     *      max=100,
     *      maxMessage= "validator.document.Tourist.max_surname"
     * )
     */
    protected $lastName;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\String(name="patronymic")
     * @Assert\Length(
     *      min=2,
     *      minMessage= "validator.document.Tourist.min_second_name",
     *      max=100,
     *      maxMessage= "validator.document.Tourist.max_second_name"
     * )
     */
    protected $patronymic;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\String(name="fullName")
     */
    protected $fullName;

    /**
     * @var \DateTime
     * @Gedmo\Versioned
     * @ODM\Date(name="birthday")
     * @Assert\Date()
     */
    protected $birthday;

    /**
     * @var \string
     * @Gedmo\Versioned
     * @ODM\String(name="sex")
     * @Assert\Choice(
     *      choices = {"male", "female", "unknown"},
     *      message =  "validator.document.Tourist.wrong_gender"
     * )
     */
    protected $sex = 'unknown';

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\String(name="phone")
     * @Assert\Length(
     *      min=2,
     *      minMessage= "validator.document.Tourist.min_phone",
     *      max=100,
     *      maxMessage= "validator.document.Tourist.max_phone"
     * )
     */
    protected $phone;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\String()
     * @Assert\Length(
     *      min=2,
     *      minMessage= "validator.document.Tourist.min_phone",
     *      max=100,
     *      maxMessage= "validator.document.Tourist.max_phone"
     * )
     */
    protected $mobilePhone;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\String()
     */
    protected $messenger;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\String(name="email")
     * @Assert\Email()
     */
    protected $email;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\String(name="note")
     * @Assert\Length(
     *      min=2,
     *      minMessage= "validator.document.Tourist.min_note",
     *      max=100,
     *      maxMessage= "validator.document.Tourist.max_note"
     * )
     */
    protected $note;

    /**
     * @var string
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\VegaBundle\Document\VegaState")
     */
    protected $citizenship;

    /**
     * @ODM\EmbedOne(targetDocument="BirthPlace")
     * @var BirthPlace
     */
    protected $birthplace;

    /**
     * @var AddressObjectDecomposed
     * @ODM\EmbedOne(targetDocument="AddressObjectDecomposed")
     */
    protected $addressObjectDecomposed;

    /**
     * @var string
     * @ODM\String
     */
    protected $addressObjectCombined;

    /**
     * @var DocumentRelation
     * @ODM\EmbedOne(targetDocument="DocumentRelation")
     */
    protected $documentRelation;

    /**
     * Set firstName
     *
     * @param string $firstName
     * @return self
     */
    public function setFirstName($firstName)
    {
        $this->firstName = mb_convert_case(mb_strtolower($firstName), MB_CASE_TITLE);
        return $this;
    }

    /**
     * Get firstName
     *
     * @return string $firstName
     */
    public function getFirstName()
    {
        if ($this->firstName == 'н/д') {
            return '';
        }
        return mb_convert_case(mb_strtolower($this->firstName), MB_CASE_TITLE);
    }

    /**
     * @param string $fullName
     * @return self
     */
    public function setFullName($fullName)
    {
        $this->fullName = $fullName;
        return $this;
    }

    /**
     * @return string $fullName
     */
    public function getFullName()
    {
        return $this->fullName;
    }

    /**
     * Set lastName
     *
     * @param string $lastName
     * @return self
     */
    public function setLastName($lastName)
    {
        $this->lastName = mb_convert_case(mb_strtolower($lastName), MB_CASE_TITLE);
        return $this;
    }

    /**
     * Get lastName
     *
     * @return string $lastName
     */
    public function getLastName()
    {
        if ($this->lastName == 'н/д') {
            return '';
        }

        return mb_convert_case(mb_strtolower($this->lastName), MB_CASE_TITLE);
    }

    /**
     * Set patronymic
     *
     * @param string $patronymic
     * @return self
     */
    public function setPatronymic($patronymic)
    {
        $this->patronymic = mb_convert_case(mb_strtolower($patronymic), MB_CASE_TITLE);
        return $this;
    }

    /**
     * Get patronymic
     *
     * @return string $patronymic
     */
    public function getPatronymic()
    {
        return mb_convert_case(mb_strtolower($this->patronymic), MB_CASE_TITLE);
    }

    /**
     * Set birthday
     *
     * @param \DateTime $birthday
     * @return self
     */
    public function setBirthday($birthday)
    {
        $this->birthday = $birthday;
        return $this;
    }

    /**
     * Get birthday
     *
     * @return \DateTime|null $birthday
     */
    public function getBirthday()
    {
        return $this->birthday;
    }

    /**
     * Set sex
     *
     * @param string $sex
     * @return self
     */
    public function setSex($sex)
    {
        $this->sex = $sex;
        return $this;
    }

    /**
     * Get sex
     *
     * @return date $sex
     */
    public function getSex()
    {
        return $this->sex;
    }

    /**
     * Get address
     *
     * @return string $address
     */
    public function getAddress()
    {
        return $this->getAddressObjectCombined();
    }

    /**
     * Set document
     *
     * @param string $document
     * @return self
     */
    public function setDocument($document)
    {
        $this->document = $document;
        return $this;
    }

    /**
     * Get document
     *
     * @return string $document
     */
    public function getDocument()
    {
        return $this->document;
    }

    /**
     * Set phone
     *
     * @param string $phone
     * @return self
     */
    public function setPhone($phone)
    {
        $this->phone = self::cleanPhone($phone);
        return $this;
    }

    /**
     * Get phone
     * @param boolean $original
     * @return string $phone
     */
    public function getPhone($original=false)
    {
        return self::formatPhone($this->phone, $original);
    }

    /**
     * @param boolean $original
     * @return string
     */
    public function getMobilePhone($original=false)
    {
        return self::formatPhone($this->mobilePhone, $original);
    }

    /**
     * @param string $mobilePhone
     */
    public function setMobilePhone($mobilePhone)
    {
        $this->mobilePhone = self::cleanPhone($mobilePhone);;
    }

    /**
     * @return string
     */
    public function getMessenger()
    {
        return $this->messenger;
    }

    /**
     * @param string $messenger
     */
    public function setMessenger($messenger)
    {
        $this->messenger = $messenger;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return self
     */
    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    /**
     * Get email
     *
     * @return string $email
     */
    public function getEmail()
    {
        return $this->email;
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

    /**
     * Gender guess
     * @return "unknown"|"male"|"female"
     */
    public function guessGender()
    {
        $end = mb_substr($this->getPatronymic(), -2, 2, 'UTF-8');

        $gender = 'unknown';
        if ($end == 'ич' || $end == 'лы') {
            $gender = 'male';
        }
        if ($end == 'на' || $end == 'зы') {
            $gender = 'female';
        }

        return $gender;
    }

    /**
     * @ODM\PrePersist
     */
    public function prePersist()
    {
        if (empty($this->sex) || $this->sex == 'unknown') {
            $this->sex = $this->guessGender();
        }

        $this->fullName = $this->generateFullName();
    }

    /**
     * @ODM\preUpdate
     */
    public function preUpdate()
    {
        if (empty($this->sex) || $this->sex == 'unknown') {
            $this->sex = $this->guessGender();
        }

        $this->fullName = $this->generateFullName();
    }

    /**
     * @return string
     */
    public function generateFullName()
    {
        $name = $this->getLastName() . ' ' . $this->getFirstName();

        return (empty($this->getPatronymic())) ? $name : $name . ' ' . $this->getPatronymic();
    }

    /**
     * @return string
     */
    public function generateFullNameWithAge()
    {
        return $this->generateFullName() . ($this->getBirthday() ? ' (' . $this->getBirthday()->format('d.m.Y') . '), возраст: ' . $this->getAge() : '');
    }

    public function __construct()
    {
        $this->packages = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Get age
     * @return int
     */
    public function getAge()
    {
        if ($this->getBirthday()) {
            $now = new \DateTime();
            $diff = $now->diff($this->getBirthday());

            return $diff->y;
        }

        return null;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->getFullName();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getFullName();
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

    public function getLastNameWithInitials()
    {
        $result = $this->getLastName() . ' ' . mb_substr($this->getFirstName(), 0, 1) . '.';

        if (!empty($this->getPatronymic())) {
            $result .= mb_substr($this->getPatronymic(), 0, 1) . '.';
        }

        return $result;
    }

    /**
     * Add order
     *
     * @param \MBH\Bundle\PackageBundle\Document\Order $order
     */
    public function addOrder(\MBH\Bundle\PackageBundle\Document\Order $order)
    {
        $this->orders[] = $order;
    }

    /**
     * Remove order
     *
     * @param \MBH\Bundle\PackageBundle\Document\Order $order
     */
    public function removeOrder(\MBH\Bundle\PackageBundle\Document\Order $order)
    {
        $this->orders->removeElement($order);
    }

    /**
     * Get orders
     *
     * @return \Doctrine\Common\Collections\Collection $orders
     */
    public function getOrders()
    {
        return $this->orders;
    }

    /**
     * @return VegaState
     */
    public function getCitizenship()
    {
        return $this->citizenship;
    }

    /**
     * @param VegaState $citizenship
     */
    public function setCitizenship(VegaState $citizenship = null)
    {
        $this->citizenship = $citizenship;
    }

    /**
     * @return BirthPlace
     */
    public function getBirthplace()
    {
        return $this->birthplace;
    }

    /**
     * @param BirthPlace $birthplace
     */
    public function setBirthplace(BirthPlace $birthplace = null)
    {
        $this->birthplace = $birthplace;
    }

    /**
     * @return AddressObjectDecomposed
     */
    public function getAddressObjectDecomposed()
    {
        return $this->addressObjectDecomposed;
    }

    /**
     * @param AddressObjectDecomposed $addressObjectDecomposed
     */
    public function setAddressObjectDecomposed(AddressObjectDecomposed $addressObjectDecomposed = null)
    {
        $this->addressObjectDecomposed = $addressObjectDecomposed;
    }

    /**
     * @return string
     */
    public function getAddressObjectCombined()
    {
        return $this->addressObjectCombined;
    }

    /**
     * @param string $addressObjectCombined
     */
    public function setAddressObjectCombined($addressObjectCombined)
    {
        $this->addressObjectCombined = $addressObjectCombined;
    }


    /**
     * @ODM\PrePersist
     * @ODM\PreUpdate
     */
    public function preSave()
    {
        if ($this->getAddressObjectDecomposed() && $this->getAddressObjectDecomposed()->getCountry() && $this->getAddressObjectDecomposed()->getRegion())
            $this->fillAddressObject();
    }

    private function fillAddressObject()
    {
        $chain = [
            $this->getAddressObjectDecomposed()->getCountry()->getName(),
            $this->getAddressObjectDecomposed()->getRegion()->getName(),
            $this->getAddressObjectDecomposed()->getCity(),
            $this->getAddressObjectDecomposed()->getStreet(),
            $this->getAddressObjectDecomposed()->getHouse(),
            $this->getAddressObjectDecomposed()->getCorpus(),
            $this->getAddressObjectDecomposed()->getFlat()
        ];

        $chain = array_map('strval', $chain);
        if (($lastKey = array_search('', $chain)) !== false)
            $chain = array_slice($chain, 0, $lastKey);

        $this->setAddressObjectCombined(implode(' ', $chain));
    }

    /**
     * @return DocumentRelation
     */
    public function getDocumentRelation()
    {
        return $this->documentRelation;
    }

    /**
     * @param DocumentRelation $documentRelation
     */
    public function setDocumentRelation(DocumentRelation $documentRelation = null)
    {
        $this->documentRelation = $documentRelation;
    }
}

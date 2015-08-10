<?php

namespace MBH\Bundle\HotelBundle\Document;

use MBH\Bundle\BaseBundle\Document\Base;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableDocument;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableDocument;
use Gedmo\Blameable\Traits\BlameableDocument;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * @ODM\Document(collection="Task", repositoryClass="TaskRepository")
 * @ODM\HasLifecycleCallbacks
 * @Gedmo\Loggable
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */
class Task extends Base
{
    const PRIORITY_LOW = 0;
    const PRIORITY_AVERAGE = 1;
    const PRIORITY_HIGH = 2;

    const DAY_DEAL_LINE = 3;

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
     * @var TaskType|null
     * @Gedmo\Versioned
     * @ODM\ReferenceOne(targetDocument="TaskType")
     * @ Assert\NotNull(message="validator.document.task.taskType_no_selected")
     */
    protected $type;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\String
     * @ Assert\NotNull()
     * @Assert\Choice(choices = {"open", "closed", "process"})
     */
    protected $status;

    /**
     * @var string
     */
    protected $previousStatus;

    /**
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\HotelBundle\Document\Room")
     */
    protected $room;

    /**
     * @var string
     * @Gedmo\Versioned
     * @ODM\String(name="description")
     * @Assert\Length(
     *      min=2,
     *      minMessage="validator.document.task.min_description",
     *      max=800,
     *      maxMessage="validator.document.task.max_description"
     * )
     */
    protected $description;

    /**
     * @var string Perform role
     * @Gedmo\Versioned
     * @ODM\String
     * @ Assert\NotNull()
     */
    protected $role;

    /**
     * @var \MBH\Bundle\UserBundle\Document\User
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\UserBundle\Document\User")
     */
    protected $performer;

    /**
     * @var int
     * @ODM\Int()
     */
    protected $priority;

    /**
     * @var integer
     * @ODM\Int
     */
    protected $number;

    /**
     * @var \DateTime
     * @ODM\Date
     */
    protected $date;

    /**
     * Set taskType
     *
     * @param TaskType|null $type
     * @return self
     */
    public function setType(TaskType $type = null)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return TaskType|null
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set status
     *
     * @param string $status
     * @return self
     */
    public function setStatus($status)
    {
        $this->previousStatus = $this->status;
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
     * Set room
     *
     * @param \MBH\Bundle\HotelBundle\Document\Room $room
     * @return self
     */
    public function setRoom(\MBH\Bundle\HotelBundle\Document\Room $room = null)
    {
        $this->room = $room;

        return $this;
    }

    /**
     * Get room
     *
     * @return \MBH\Bundle\HotelBundle\Document\Room $room
     */
    public function getRoom()
    {
        return $this->room;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return self
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string $description
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set role
     *
     * @param string $role
     * @return self
     */
    public function setRole($role)
    {
        $this->role = $role;

        return $this;
    }

    /**
     * Get role
     *
     * @return string $role
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * @return \MBH\Bundle\UserBundle\Document\User
     */
    public function getPerformer()
    {
        return $this->performer;
    }

    /**
     * @param \MBH\Bundle\UserBundle\Document\User $performer
     */
    public function setPerformer(\MBH\Bundle\UserBundle\Document\User $performer)
    {
        $this->performer = $performer;
    }

    /**
     * @return int
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * @param int $priority
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;
    }

    /**
     * @return int
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * @param int $number
     */
    public function setNumber($number)
    {
        $this->number = $number;
    }

    /**
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param \DateTime|null $date
     */
    public function setDate($date = null)
    {
        $this->date = $date;
    }

    /**
     * @Assert\Callback
     * @author Aleksandr Arofikin <sashaaro@gmail.com>
     */
    public function validate(ExecutionContextInterface $context)
    {
        /*if (!$this->isStatusChainValid()) {
            $context->buildViolation('Settled status is not correct')->atPath('status')->addViolation();
        };*/
        if(!$this->role && !$this->performer) {
            $context->buildViolation('validator.task.assignment')->addViolation();
        }
    }

    /**
     * @return bool
     */
    public function isExpired()
    {
        $now = time();
        return $this->status !== 'open' && ($this->getDate() ?
            $this->getDate()->getTimestamp() < $now :
            $this->getCreatedAt()->modify('+ '.self::DAY_DEAL_LINE.' days')->getTimestamp() < $now);
    }

    /**
     * Validate status order. Consider previous status and current status
     * @return boolean
     *
     * @author Aleksandr Arofikin <sashaaro@gmail.com>
     */
    private function isStatusChainValid()
    {
        return !$this->previousStatus || ($this->status == 'process' && $this->previousStatus == 'open' || $this->status == 'closed' && $this->previousStatus == 'process');
    }
}

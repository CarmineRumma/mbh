<?php

namespace MBH\Bundle\PackageBundle\Document\Partials;

use MBH\Bundle\PackageBundle\Document\DeleteReason;

trait DeleteReasonTrait {

    /**
     * @Gedmo\Versioned
     * @ODM\ReferenceOne(targetDocument="MBH\Bundle\PackageBundle\Document\DeleteReason")
     */
    protected $deleteReason;

    /**
     * Set delete reason id
     *
     * @param DeleteReason $deleteReason
     * @return self
     */
    public function setDeleteReason(DeleteReason $deleteReason)
    {
        $this->deleteReason = $deleteReason;

        return $this;
    }

    /**
     * Get delete reason
     *
     * @return mixed
     */
    public function getDeleteReason()
    {
        return $this->deleteReason;
    }
}
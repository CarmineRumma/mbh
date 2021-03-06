<?php

namespace MBH\Bundle\PackageBundle\Document;

use Doctrine\ODM\MongoDB\DocumentRepository;

/**
 * Class DeleteReasonRepository
 */
class DeleteReasonRepository extends DocumentRepository
{
    /**
     * Get not deleted delete reasons
     *
     * @return mixed
     */
   public function getNotDeleted()
   {
       return $this->createQueryBuilder()->field('deletedAt')->equals(null)->getQuery()->execute();
   }

    /**
     * The reason delete
     * Get reason delete by id
     *
     * @param $deleteReasonId
     * @return array|null|object
     */
   public function getSelectedReason($deleteReasonId)
   {
       return $this->createQueryBuilder()->field('id')->equals($deleteReasonId)->getQuery()->getSingleResult();
   }
}
<?php
/**
 * Created by PhpStorm.
 * User: webmalc
 * Date: 10/8/15
 * Time: 10:57 AM
 */

namespace MBH\Bundle\UserBundle\EventListener;

use Doctrine\ODM\MongoDB\Event\LoadClassMetadataEventArgs;

class ClassMetadataListener
{
    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs)
    {
        $classMetadata = $eventArgs->getClassMetadata();
        // Override FOS to not have unique emails
        if ($classMetadata->reflClass->name == 'FOS\UserBundle\Model\User') {
            foreach ($classMetadata->indexes as $i => $index) {
                if (count($index['keys']) === 1 && isset($index['keys']['emailCanonical'])) {
                    $classMetadata->indexes[$i]['options']['unique'] = false;
                }
                if (count($index['keys']) === 1 && isset($index['keys']['usernameCanonical'])) {
                    $classMetadata->indexes[$i]['options']['unique'] = false;
                }
            }
        }
        if ($classMetadata->reflClass->name == 'FOS\UserBundle\Model\Group') {
            foreach ($classMetadata->indexes as $i => $index) {
                if (count($index['keys']) === 1 && isset($index['keys']['name'])) {
                    $classMetadata->indexes[$i]['options']['unique'] = false;
                }
            }
        }
    }
}
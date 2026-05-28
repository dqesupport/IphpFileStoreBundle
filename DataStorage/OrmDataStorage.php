<?php

namespace Iphp\FileStoreBundle\DataStorage;

use Iphp\FileStoreBundle\DataStorage\DataStorageInterface;
use Doctrine\Common\EventArgs;
use Doctrine\Persistence\Proxy;

/**
 * Orm Data Storage
 *
 * @author Vitiko <vitiko@mail.ru>
 */
class OrmDataStorage implements DataStorageInterface
{
    /**
     * {@inheritDoc}
     */
    public function getObjectFromArgs(EventArgs $e)
    {
        return $e->getObject();
    }

    /**
     * {@inheritDoc}
     */
    public function recomputeChangeSet(EventArgs $e)
    {
        $obj = $this->getObjectFromArgs($e);

        /**
         * var \Doctrine\ORM\EntityManager
         */
        $em = $e->getObjectManager();

        $uow = $em->getUnitOfWork();
        $metadata = $em->getClassMetadata(get_class($obj));
        $uow->recomputeSingleEntityChangeSet($metadata, $obj);
    }

    /**
     * {@inheritDoc}
     */
    public function getReflectionClass($obj)
    {
        if ($obj instanceof Proxy) {
            return new \ReflectionClass(get_parent_class($obj));
        }

        return new \ReflectionClass($obj);
    }


    /**
     * {@inheritDoc}
     */
    public function postFlush($obj, EventArgs $args)
    {
        $args->getObjectManager()->persist($obj);
        $args->getObjectManager()->flush();
    }


    public function previusFieldDataIfChanged($fieldName, EventArgs $args)
    {
        return $args->hasChangedField($fieldName) ? $args->getOldValue($fieldName) : null;
    }


}

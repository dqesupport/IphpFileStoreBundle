<?php

namespace Iphp\FileStoreBundle\DataStorage;

use Doctrine\Common\EventArgs;
use Doctrine\Persistence\Event\LifecycleEventArgs;

/**
 * DataStorageInterface.
 *
 * @author Vitiko <vitiko@mail.ru>
 */
interface DataStorageInterface
{
    /**
     * Gets the mapped object from the event arguments.
     *
     * @param  \Doctrine\Persistence\Event\LifecycleEventArgs $e The event arguments.
     * @return object    The mapped object.
     */
    public function getObjectFromArgs(EventArgs $e);

    /**
     * Recomputes the change set for the object.
     *
     * @param \Doctrine\Persistence\Event\LifecycleEventArgs $e The event arguments.
     */
    public function recomputeChangeSet(EventArgs $e);

    /**
     * Gets the reflection class for the object taking
     * proxies into account.
     *
     * @param  object           $obj The object.
     * @return \ReflectionClass The reflection class.
     */
    public function getReflectionClass($obj);


    public function postFlush ($obj, EventArgs $args);


    public function previusFieldDataIfChanged ($fieldName, EventArgs $args);
}

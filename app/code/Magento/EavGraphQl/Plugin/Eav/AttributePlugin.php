<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\EavGraphQl\Plugin\Eav;

use Magento\Eav\Model\Entity\Attribute;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Event\ManagerInterface;

/**
 * EAV plugin runs page cache clean and provides proper EAV identities.
 */
class AttributePlugin implements IdentityInterface
{
    /**
     * Application Event Dispatcher
     *
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * @var array
     */
    private $identities = [];

    /**
     * @param ManagerInterface $eventManager
     */
    public function __construct(ManagerInterface $eventManager)
    {
        $this->eventManager = $eventManager;
    }

    /**
     * Clean cache by relevant tags after entity save.
     *
     * @param Attribute $subject
     * @param Attribute $result
     *
     * @return Attribute
     */
    public function afterSave(Attribute $subject, Attribute $result): Attribute
    {
        if (!$subject->isObjectNew()) {
            $this->triggerCacheClean($subject);
        }
        return $result;
    }

    /**
     * Clean cache by relevant tags after entity is deleted and afterDelete handler is executed.
     *
     * @param Attribute $subject
     * @param Attribute $result
     *
     * @return Attribute
     */
    public function afterAfterDelete(Attribute $subject, Attribute $result) : Attribute
    {
        $this->triggerCacheClean($subject);
        return $result;
    }

    /**
     * Trigger cache clean event in event manager.
     *
     * @param Attribute $entity
     * @return void
     */
    private function triggerCacheClean(Attribute $entity): void
    {
        $this->identities[] = sprintf(
            "%s_%s",
            Attribute::CACHE_TAG,
            $entity->getAttributeCode()
        );
        $this->eventManager->dispatch('clean_cache_by_tags', ['object' => $this]);
    }

    /**
     * @inheritDoc
     */
    public function getIdentities()
    {
        return $this->identities;
    }
}

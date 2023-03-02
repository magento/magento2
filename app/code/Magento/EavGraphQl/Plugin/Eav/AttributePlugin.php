<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\EavGraphQl\Plugin\Eav;

use Magento\Eav\Model\Entity\Attribute;
use Magento\Eav\Model\Entity\Attribute as EavAttribute;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Event\ManagerInterface;

/**
 * EAV plugin runs page cache clean and provides peoper EAV identities.
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
     * Clean cache by relevant tags.
     *
     * @param Attribute $subject
     * @param Attribute $result
     * @return Attribute
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterSave(
        Attribute $subject,
        Attribute $result
    ): Attribute
    {
        $this->identities[] = sprintf(
            "%s_%s",
            EavAttribute::CACHE_TAG,
            $subject->getAttributeCode()
        );
        $this->eventManager->dispatch('clean_cache_by_tags', ['object' => $this]);
        return $result;
    }

    /**
     * @inheirtdoc
     */
    public function getIdentities()
    {
        return $this->identities;
    }
}

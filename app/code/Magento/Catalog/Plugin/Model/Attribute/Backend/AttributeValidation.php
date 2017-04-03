<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Plugin\Model\Attribute\Backend;

use Magento\Store\Model\Store;

class AttributeValidation
{
    /** @var \Magento\Store\Model\StoreManagerInterface */
    private $storeManager;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param array $allowedEntityTypes
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        $allowedEntityTypes = []
    ) {
        $this->storeManager = $storeManager;
        $this->allowedEntityTypes = $allowedEntityTypes;
    }

    /**
     * @param \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend $subject
     * @param \Closure $proceed
     * @param \Magento\Framework\DataObject $entity
     * @return bool
     */
    public function aroundValidate(
        \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend $subject,
        \Closure $proceed,
        \Magento\Framework\DataObject $entity
    ) {
        $isAllowedType = !empty(array_filter(array_map(function ($allowedEntity) use ($entity) {
            return $entity instanceof $allowedEntity;
        }, $this->allowedEntityTypes)));

        if ($isAllowedType && $this->storeManager->getStore()->getId() !== Store::DEFAULT_STORE_ID) {
            $attrCode = $subject->getAttribute()->getAttributeCode();
            // Null is meaning "no value" which should be overridden by value from default scope
            if (array_key_exists($attrCode, $entity->getData()) && $entity->getData($attrCode) === null) {
                return true;
            }
        }

        return $proceed($entity);
    }
}

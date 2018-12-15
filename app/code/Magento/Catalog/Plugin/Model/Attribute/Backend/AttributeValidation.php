<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Plugin\Model\Attribute\Backend;

use Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend;
use Magento\Framework\DataObject;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Attribute validation
 */
class AttributeValidation
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var array
     */
    private $allowedEntityTypes;

    /**
     * @param StoreManagerInterface $storeManager
     * @param array $allowedEntityTypes
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        $allowedEntityTypes = []
    ) {
        $this->storeManager = $storeManager;
        $this->allowedEntityTypes = $allowedEntityTypes;
    }

    /**
     * Around validate
     *
     * @param AbstractBackend $subject
     * @param \Closure $proceed
     * @param DataObject $entity
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @return bool
     */
    public function aroundValidate(
        AbstractBackend $subject,
        \Closure $proceed,
        DataObject $entity
    ) {
        $isAllowedType = !empty(array_filter(array_map(function ($allowedEntity) use ($entity) {
            return $entity instanceof $allowedEntity;
        }, $this->allowedEntityTypes)));

        if ($isAllowedType && (int) $this->storeManager->getStore()->getId() !== Store::DEFAULT_STORE_ID) {
            $attrCode = $subject->getAttribute()->getAttributeCode();
            // Null is meaning "no value" which should be overridden by value from default scope
            if (array_key_exists($attrCode, $entity->getData()) && $entity->getData($attrCode) === null) {
                return true;
            }
        }

        return $proceed($entity);
    }
}

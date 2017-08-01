<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Model;

use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;

/**
 * Attribute Metadata data provider class
 * @since 2.0.0
 */
class AttributeMetadataDataProvider
{
    /**
     * @var \Magento\Eav\Model\Config
     * @since 2.0.0
     */
    private $eavConfig;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Form\Attribute\CollectionFactory
     * @since 2.0.0
     */
    private $attrFormCollectionFactory;

    /**
     * @var \Magento\Store\Model\StoreManager
     * @since 2.0.0
     */
    private $storeManager;

    /**
     * Initialize data provider with data source
     *
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Customer\Model\ResourceModel\Form\Attribute\CollectionFactory $attrFormCollectionFactory
     * @param \Magento\Store\Model\StoreManager $storeManager
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Customer\Model\ResourceModel\Form\Attribute\CollectionFactory $attrFormCollectionFactory,
        \Magento\Store\Model\StoreManager $storeManager
    ) {
        $this->eavConfig = $eavConfig;
        $this->attrFormCollectionFactory = $attrFormCollectionFactory;
        $this->storeManager = $storeManager;
    }

    /**
     * Get attribute model for a given entity type and code
     *
     * @param string $entityType
     * @param string $attributeCode
     * @return false|AbstractAttribute
     * @since 2.0.0
     */
    public function getAttribute($entityType, $attributeCode)
    {
        return $this->eavConfig->getAttribute($entityType, $attributeCode);
    }

    /**
     * Get all attribute codes for a given entity type and attribute set
     *
     * @param string $entityType
     * @param int $attributeSetId
     * @param string|null $storeId
     * @return array Attribute codes
     * @since 2.0.0
     */
    public function getAllAttributeCodes($entityType, $attributeSetId = 0, $storeId = null)
    {
        if (null === $storeId) {
            $storeId = $this->storeManager->getStore()->getId();
        }
        $object = new \Magento\Framework\DataObject(
            [
                'store_id' => $storeId,
                'attribute_set_id' => $attributeSetId,
            ]
        );
        return $this->eavConfig->getEntityAttributeCodes($entityType, $object);
    }

    /**
     * Load collection with filters applied
     *
     * @param string $entityType
     * @param string $formCode
     * @return \Magento\Customer\Model\ResourceModel\Form\Attribute\Collection
     * @since 2.0.0
     */
    public function loadAttributesCollection($entityType, $formCode)
    {
        $attributesFormCollection = $this->attrFormCollectionFactory->create();
        $attributesFormCollection->setStore($this->storeManager->getStore())
            ->setEntityType($entityType)
            ->addFormCodeFilter($formCode)
            ->setSortOrder();

        return $attributesFormCollection;
    }
}

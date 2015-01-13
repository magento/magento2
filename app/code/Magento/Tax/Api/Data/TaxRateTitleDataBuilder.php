<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Api\Data;

/**
 * DataBuilder class for \Magento\Tax\Api\Data\TaxRateTitleInterface
 * @codeCoverageIgnore
 */
class TaxRateTitleDataBuilder extends \Magento\Framework\Api\Builder
{
    /**
     * Initialize the builder
     *
     * @param \Magento\Framework\Api\ObjectFactory $objectFactory
     * @param \Magento\Framework\Api\MetadataServiceInterface $metadataService
     * @param \Magento\Framework\Api\AttributeDataBuilder $attributeValueBuilder
     * @param \Magento\Framework\Reflection\DataObjectProcessor $objectProcessor
     * @param \Magento\Framework\Reflection\TypeProcessor $typeProcessor
     * @param \Magento\Framework\Serialization\DataBuilderFactory $dataBuilderFactory
     * @param \Magento\Framework\ObjectManager\ConfigInterface $objectManagerConfig
     * @param string|null $modelClassInterface
     */
    public function __construct(
        \Magento\Framework\Api\ObjectFactory $objectFactory,
        \Magento\Framework\Api\MetadataServiceInterface $metadataService,
        \Magento\Framework\Api\AttributeDataBuilder $attributeValueBuilder,
        \Magento\Framework\Reflection\DataObjectProcessor $objectProcessor,
        \Magento\Framework\Reflection\TypeProcessor $typeProcessor,
        \Magento\Framework\Serialization\DataBuilderFactory $dataBuilderFactory,
        \Magento\Framework\ObjectManager\ConfigInterface $objectManagerConfig,
        $modelClassInterface = null
    ) {
        parent::__construct(
            $objectFactory,
            $metadataService,
            $attributeValueBuilder,
            $objectProcessor,
            $typeProcessor,
            $dataBuilderFactory,
            $objectManagerConfig,
            'Magento\Tax\Api\Data\TaxRateTitleInterface'
        );
    }

    /**
     * @param string $storeId
     * @return $this
     */
    public function setStoreId($storeId)
    {
        $this->_set('store_id', $storeId);
        return $this;
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setValue($value)
    {
        $this->_set('value', $value);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function create()
    {
        $object = parent::create();
        $object->setDataChanges(true);
        return $object;
    }
}

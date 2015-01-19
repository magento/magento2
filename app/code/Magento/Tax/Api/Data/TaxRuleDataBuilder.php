<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Api\Data;

/**
 * DataBuilder class for \Magento\Tax\Api\Data\TaxRuleInterface
 * @codeCoverageIgnore
 */
class TaxRuleDataBuilder extends \Magento\Framework\Api\Builder
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
            'Magento\Tax\Api\Data\TaxRuleInterface'
        );
    }

    /**
     * @param int|null $id
     * @return $this
     */
    public function setId($id)
    {
        $this->_set('id', $id);
        return $this;
    }

    /**
     * @param string $code
     * @return $this
     */
    public function setCode($code)
    {
        $this->_set('code', $code);
        return $this;
    }

    /**
     * @param int $priority
     * @return $this
     */
    public function setPriority($priority)
    {
        $this->_set('priority', $priority);
        return $this;
    }

    /**
     * @param int $position
     * @return $this
     */
    public function setPosition($position)
    {
        $this->_set('position', $position);
        return $this;
    }

    /**
     * @param int $customerTaxClassIds
     * @return $this
     */
    public function setCustomerTaxClassIds($customerTaxClassIds)
    {
        $this->_set('customer_tax_class_ids', $customerTaxClassIds);
        return $this;
    }

    /**
     * @param int $productTaxClassIds
     * @return $this
     */
    public function setProductTaxClassIds($productTaxClassIds)
    {
        $this->_set('product_tax_class_ids', $productTaxClassIds);
        return $this;
    }

    /**
     * @param int $taxRateIds
     * @return $this
     */
    public function setTaxRateIds($taxRateIds)
    {
        $this->_set('tax_rate_ids', $taxRateIds);
        return $this;
    }

    /**
     * @param bool|null $calculateSubtotal
     * @return $this
     */
    public function setCalculateSubtotal($calculateSubtotal)
    {
        $this->_set('calculate_subtotal', $calculateSubtotal);
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

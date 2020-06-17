<?php
/**
 * Product Attribute Group mapper plugin. Adds Configurable product information
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Model\Entity\Product\Attribute\Group\AttributeMapper;

use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\AttributeFactory;
use Magento\Framework\Registry;

class Plugin
{
    /**
     * @var \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\AttributeFactory
     */
    protected $attributeFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var array
     */
    protected $configurableAttributes;

    /**
     * @param AttributeFactory $attributeFactory
     * @param Registry $registry
     */
    public function __construct(AttributeFactory $attributeFactory, Registry $registry)
    {
        $this->registry = $registry;
        $this->attributeFactory = $attributeFactory;
    }

    /**
     * Add is_configurable field to attribute presentation
     *
     * @param \Magento\Catalog\Model\Entity\Product\Attribute\Group\AttributeMapperInterface $subject
     * @param array $result
     * @param \Magento\Eav\Model\Entity\Attribute $attribute
     *
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterMap(
        \Magento\Catalog\Model\Entity\Product\Attribute\Group\AttributeMapperInterface $subject,
        $result,
        \Magento\Eav\Model\Entity\Attribute $attribute
    ) {
        $setId = $this->registry->registry('current_attribute_set')->getId();
        if (!isset($this->configurableAttributes[$setId])) {
            $this->configurableAttributes[$setId] = $this->attributeFactory->create()->getUsedAttributes($setId);
        }
        $result['is_configurable'] = (int)in_array(
            $attribute->getAttributeId(),
            $this->configurableAttributes[$setId]
        );
        return $result;
    }
}

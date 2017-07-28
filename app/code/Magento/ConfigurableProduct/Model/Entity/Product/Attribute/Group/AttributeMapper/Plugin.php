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

/**
 * Class \Magento\ConfigurableProduct\Model\Entity\Product\Attribute\Group\AttributeMapper\Plugin
 *
 * @since 2.0.0
 */
class Plugin
{
    /**
     * @var \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\AttributeFactory
     * @since 2.0.0
     */
    protected $attributeFactory;

    /**
     * @var \Magento\Framework\Registry
     * @since 2.0.0
     */
    protected $registry;

    /**
     * @var array
     * @since 2.0.0
     */
    protected $configurableAttributes;

    /**
     * @param AttributeFactory $attributeFactory
     * @param Registry $registry
     * @since 2.0.0
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
     * @param callable $proceed
     * @param \Magento\Eav\Model\Entity\Attribute $attribute
     *
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.0.0
     */
    public function aroundMap(
        \Magento\Catalog\Model\Entity\Product\Attribute\Group\AttributeMapperInterface $subject,
        \Closure $proceed,
        \Magento\Eav\Model\Entity\Attribute $attribute
    ) {
        $setId = $this->registry->registry('current_attribute_set')->getId();
        $result = $proceed($attribute);
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

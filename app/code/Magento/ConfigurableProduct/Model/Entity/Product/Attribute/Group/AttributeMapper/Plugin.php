<?php
/**
 * Product Attribute Group mapper plugin. Adds Configurable product information
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Model\Entity\Product\Attribute\Group\AttributeMapper;

use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\AttributeFactory;
use Magento\Framework\Registry;
use Magento\Catalog\Model\Entity\Product\Attribute\Group\AttributeMapperInterface;

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
     * @var int|string
     */
    private $setId;

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
     * @param AttributeMapperInterface $subject
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeMap(AttributeMapperInterface $subject)
    {
        $this->setId = $this->registry->registry('current_attribute_set')->getId();
    }

    /**
     * Add is_configurable field to attribute presentation
     *
     * @param AttributeMapperInterface $subject
     * @param array $result
     * @param \Magento\Eav\Model\Entity\Attribute $attribute
     *
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterMap(
        AttributeMapperInterface $subject,
        array $result,
        \Magento\Eav\Model\Entity\Attribute $attribute
    ) {
        if (!isset($this->configurableAttributes[$this->setId])) {
            $this->configurableAttributes[$this->setId] = $this->attributeFactory->create()->getUsedAttributes(
                $this->setId
            );
        }
        $result['is_configurable'] = (int)in_array(
            $attribute->getAttributeId(),
            $this->configurableAttributes[$this->setId]
        );
        return $result;
    }
}

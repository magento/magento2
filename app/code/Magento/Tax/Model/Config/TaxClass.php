<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Model\Config;

/**
 * TaxClass Config
 * @since 2.0.0
 */
class TaxClass extends \Magento\Framework\App\Config\Value
{
    /**
     * @var \Magento\Config\Model\ResourceModel\Config
     * @since 2.0.0
     */
    protected $resourceConfig;

    /**
     * @var \Magento\Eav\Model\Entity\AttributeFactory
     * @since 2.0.0
     */
    protected $attributeFactory;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\Config\Model\ResourceModel\Config $resourceConfig
     * @param \Magento\Eav\Model\Entity\AttributeFactory $attributeFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Config\Model\ResourceModel\Config $resourceConfig,
        \Magento\Eav\Model\Entity\AttributeFactory $attributeFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->resourceConfig = $resourceConfig;
        $this->attributeFactory = $attributeFactory;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * Update the default product tax class
     *
     * @return $this
     * @since 2.0.0
     */
    public function afterSave()
    {
        $attributeCode = "tax_class_id";

        $attribute = $this->attributeFactory->create();
        $attribute->loadByCode(\Magento\Catalog\Model\Product::ENTITY, $attributeCode);
        if (!$attribute->getId()) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Invalid attribute %1', $attributeCode));
        }
        $attribute->setData("default_value", $this->getData('value'));
        $attribute->save();

        return parent::afterSave();
    }
}

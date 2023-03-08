<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Model\Config;

use Magento\Catalog\Model\Product;
use Magento\Config\Model\ResourceModel\Config;
use Magento\Eav\Model\Entity\AttributeFactory;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;

/**
 * TaxClass Config
 */
class TaxClass extends Value
{
    /**
     * @param Context $context
     * @param Registry $registry
     * @param ScopeConfigInterface $config
     * @param TypeListInterface $cacheTypeList
     * @param Config $resourceConfig
     * @param AttributeFactory $attributeFactory
     * @param AbstractResource $resource
     * @param AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        protected readonly Config $resourceConfig,
        protected readonly AttributeFactory $attributeFactory,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * Update the default product tax class
     *
     * @return $this
     */
    public function afterSave()
    {
        $attributeCode = "tax_class_id";

        $attribute = $this->attributeFactory->create();
        $attribute->loadByCode(Product::ENTITY, $attributeCode);
        if (!$attribute->getId()) {
            throw new LocalizedException(__('Invalid attribute %1', $attributeCode));
        }
        $attribute->setData("default_value", $this->getData('value'));
        $attribute->save();

        return parent::afterSave();
    }
}

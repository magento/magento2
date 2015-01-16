<?php
/**
 * List of suggested attributes
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Model;

class SuggestedAttributeList
{
    /**
     * Attribute collection factory
     *
     * @var \Magento\Catalog\Model\Resource\Product\Attribute\CollectionFactory
     */
    protected $_attributeColFactory;

    /**
     * Catalog resource helper
     *
     * @var \Magento\Catalog\Model\Resource\Helper
     */
    protected $_resourceHelper;

    /**
     * @param \Magento\Catalog\Model\Resource\Product\Attribute\CollectionFactory $attributeColFactory
     * @param \Magento\Catalog\Model\Resource\Helper $resourceHelper
     */
    public function __construct(
        \Magento\Catalog\Model\Resource\Product\Attribute\CollectionFactory $attributeColFactory,
        \Magento\Catalog\Model\Resource\Helper $resourceHelper
    ) {
        $this->_attributeColFactory = $attributeColFactory;
        $this->_resourceHelper = $resourceHelper;
    }

    /**
     * Retrieve list of attributes with admin store label containing $labelPart
     *
     * @param string $labelPart
     * @return \Magento\Catalog\Model\Resource\Product\Attribute\Collection
     */
    public function getSuggestedAttributes($labelPart)
    {
        $escapedLabelPart = $this->_resourceHelper->addLikeEscape($labelPart, ['position' => 'any']);
        /** @var $collection \Magento\Catalog\Model\Resource\Product\Attribute\Collection */
        $collection = $this->_attributeColFactory->create();
        $collection->addFieldToFilter(
            'frontend_input',
            'select'
        )->addFieldToFilter(
            'frontend_label',
            ['like' => $escapedLabelPart]
        )->addFieldToFilter(
            'is_configurable',
            [["eq" => 1], ['null' => true]]
        )->addFieldToFilter(
            'is_user_defined',
            1
        )->addFieldToFilter(
            'is_global',
            \Magento\Catalog\Model\Resource\Eav\Attribute::SCOPE_GLOBAL
        );

        $result = [];
        $types = [
            \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE,
            \Magento\Catalog\Model\Product\Type::TYPE_VIRTUAL,
            \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE,
        ];
        foreach ($collection->getItems() as $id => $attribute) {
            /** @var $attribute \Magento\Catalog\Model\Resource\Eav\Attribute */
            if (!$attribute->getApplyTo() || count(array_diff($types, $attribute->getApplyTo())) === 0) {
                $result[$id] = [
                    'id' => $attribute->getId(),
                    'label' => $attribute->getFrontendLabel(),
                    'code' => $attribute->getAttributeCode(),
                    'options' => $attribute->getSource()->getAllOptions(false),
                ];
            }
        }
        return $result;
    }
}

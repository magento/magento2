<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Swatches\Plugin\Catalog;

use Magento\Catalog\Model\Product;
use Magento\Eav\Api\Data\AttributeOptionInterface;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute\OptionManagement;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory as OptionCollectionFactory;
use Magento\Swatches\Helper\Data as SwatchHelper;

/**
 * Class AddSwatchDataToAddOption
 *
 * @package Magento\Swatches\Plugin\Catalog
 */
class AddSwatchDataToAddOption
{
    const SWATCH_VISUAL = 'visual';
    const SWATCH_TEXT = 'text';
    const PREFIX_OPTION = 'option';
    const PREFIX_SWATCH = 'swatch';

    /**
     * @var Config
     */
    private $eavConfig;

    /**
     * @var SwatchHelper
     */
    private $swatchHelper;

    /**
     * @var OptionCollectionFactory
     */
    private $optionCollectionFactory;

    /**
     * @param Config                  $eavConfig
     * @param SwatchHelper            $swatchHelper
     * @param OptionCollectionFactory $attrOptionCollectionFactory
     */
    public function __construct(
        Config $eavConfig,
        SwatchHelper $swatchHelper,
        OptionCollectionFactory $attrOptionCollectionFactory
    ) {
        $this->eavConfig               = $eavConfig;
        $this->swatchHelper            = $swatchHelper;
        $this->optionCollectionFactory = $attrOptionCollectionFactory;
    }

    /**
     * @param OptionManagement $subject
     * @param int $entityType
     * @param string $attributeCode
     * @param AttributeOptionInterface $option
     *
     * @return array []
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeAdd(OptionManagement $subject, $entityType, $attributeCode, $option)
    {
        $attribute = $this->eavConfig->getAttribute(Product::ENTITY, $attributeCode);

        if (! $this->isSwatch($attribute)) {
            return [$entityType, $attributeCode, $option];
        }

        $swatchType = $this->getSwatchType($attribute);
        $optionKey  = self::PREFIX_OPTION . $swatchType;
        $swatchKey  = self::PREFIX_SWATCH . $swatchType;

        if ($attribute->getData($optionKey) !== null || $attribute->getData($swatchKey) !== null) {
            return [$entityType, $attributeCode, $option];
        }

        $optionId    = $option->getValue();
        $optionOrder = $option->getSortOrder();
        $prefix      = $optionId;
        if ($optionId === '') {
            $attributeData = $this->prepareAttributeDataForNewOption($attribute->getAttributeId(), $optionKey);
            $optionId      = count($attributeData[$optionKey]['value']);
            if ($optionOrder === null) {
                $optionOrder = $optionId + 1;
            }
            $prefix = 'option_' . $optionId;
            $option->setValue($prefix);
        }

        $storeLabels                                  = $option->getStoreLabels();
        $attributeData[$optionKey]['delete'][$prefix] = '';
        $attributeData[$optionKey]['order'][$prefix]  = $optionOrder;
        if ($swatchType === self::SWATCH_VISUAL) {
            $attributeData[$swatchKey]['value'][$prefix] = '';
        }
        foreach ($storeLabels as $storeLabel) {
            $attributeData[$optionKey]['value'][$prefix][$storeLabel->getStoreId()] = $storeLabel->getLabel();
            if ($swatchType === self::SWATCH_TEXT) {
                $attributeData[$swatchKey]['value'][$prefix][$storeLabel->getStoreId()] = $storeLabel->getLabel();
            }
        }
        $attribute->addData($attributeData);

        return [$entityType, $attributeCode, $option];
    }

    /**
     * @param $attribute
     *
     * @return boolean
     */
    protected function isSwatch($attribute)
    {
        if ($this->swatchHelper->isVisualSwatch($attribute)) {
            return true;
        } elseif ($this->swatchHelper->isTextSwatch($attribute)) {
            return true;
        }

        return false;
    }

    /**
     * @param $attribute
     *
     * @return null|string
     */
    protected function getSwatchType($attribute)
    {
        if ($this->swatchHelper->isVisualSwatch($attribute)) {
            return self::SWATCH_VISUAL;
        } elseif ($this->swatchHelper->isTextSwatch($attribute)) {
            return self::SWATCH_TEXT;
        }

        return null;
    }

    /**
     * @param int    $attributeId
     * @param string $optionKey
     *
     * @return array
     */
    protected function prepareAttributeDataForNewOption($attributeId, $optionKey)
    {
        $options    = $this->getOptionsByAttributeIdWithSortOrder($attributeId);
        return $this->getOptionsForSwatch($options, $optionKey);
    }

    /**
     * @param []     $options
     * @param string $optionKey
     *
     * @return array
     */
    protected function getOptionsForSwatch(array $options, $optionKey)
    {
        $optionsArray = [];

        if (count($options) === 0) {
            $optionsArray[$optionKey]['value']  = [];
            $optionsArray[$optionKey]['delete'] = [];

            return $optionsArray;
        }

        foreach ($options as $sortOrder => $optionId) {
            $optionsArray[$optionKey]['value'][$optionId]  = $this->getStoreLabels($optionId);
            $optionsArray[$optionKey]['delete'][$optionId] = '';
            $optionsArray[$optionKey]['order'][$optionId]  = (string)$sortOrder;
        }

        return $optionsArray;
    }

    /**
     * @param int $optionId
     *
     * @return array
     */
    protected function getStoreLabels($optionId)
    {
        $optionCollectionFactory = $this->optionCollectionFactory->create();
        $connection              = $optionCollectionFactory->getConnection();
        $optionValueTable        = $optionCollectionFactory->getTable('eav_attribute_option_value');
        $select                  = $connection->select()->from(
            ['eaov' => $optionValueTable],
            []
        )->where('option_id = ?', $optionId)->columns(['store_id', 'value']);

        return $connection->fetchPairs($select);
    }

    /**
     * @param int $attributeId
     *
     * @return array
     */
    protected function getOptionsByAttributeIdWithSortOrder($attributeId)
    {
        $optionCollectionFactory = $this->optionCollectionFactory->create();
        $options                 = $optionCollectionFactory
            ->setAttributeFilter($attributeId)
            ->setPositionOrder()
            ->addFieldToSelect('option_id')
            ->getAllIds();

        return array_values($options);
    }
}

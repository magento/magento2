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
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory as OptionCollection;
use Magento\Swatches\Helper\Data as SwatchHelper;

class AddSwatchDataToAddOption
{
    const SWATCH_VISUAL = 'visual';
    const SWATCH_TEXT = 'text';
    const PREFIX_OPTION = 'option';
    const PREFIX_SWATCH = 'swatch';

    /**
     * @var Config
     */
    protected $eavConfig;

    /**
     * @var SwatchHelper
     */
    protected $swatchHelper;

    /**
     * @var OptionCollection
     */
    protected $optionCollection;

    /**
     * @param Config           $eavConfig
     * @param SwatchHelper     $swatchHelper
     * @param OptionCollection $attrOptionCollectionFactory
     */
    public function __construct(
        Config $eavConfig,
        SwatchHelper $swatchHelper,
        OptionCollection $attrOptionCollectionFactory
    ) {
        $this->eavConfig                   = $eavConfig;
        $this->swatchHelper                = $swatchHelper;
        $this->optionCollection = $attrOptionCollectionFactory;
    }

    /**
     * @param OptionManagement         $subject
     * @param int                      $entityType
     * @param string                   $attributeCode
     * @param AttributeOptionInterface $option
     *
     * @return []
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeAdd(OptionManagement $subject, $entityType, $attributeCode, $option)
    {
        $attribute = $this->eavConfig->getAttribute(Product::ENTITY, $attributeCode);

        $isSwatch = $this->isSwatch($attribute);
        $optionKey = self::PREFIX_OPTION . $isSwatch;
        $swatchKey = self::PREFIX_SWATCH . $isSwatch;

        if (!empty($isSwatch) && $attribute->getData($optionKey) === null
            && $attribute->getData($swatchKey) === null) {
            $optionId    = $option->getValue();
            $optionOrder = $option->getSortOrder();
            $prefix = $optionId;
            if ($optionId === '') {
                $attributeData = $this->prepareAttributeDataForNewOption($attribute->getAttributeId(), $optionKey);
                $optionId      = count($attributeData[$optionKey]['value']);
                if ($optionOrder === null) {
                    $optionOrder = $optionId + 1;
                }
                $prefix        = 'option_' . $optionId;
                $option->setValue($prefix);
            }

            $storeLabels = $option->getStoreLabels();
            $attributeData[$optionKey]['delete'][$prefix] = '';
            $attributeData[$optionKey]['order'][$prefix]  = $optionOrder;
            if ($isSwatch === self::SWATCH_VISUAL) {
                $attributeData[$swatchKey]['value'][$prefix] = '';
            }
            foreach ($storeLabels as $storeLabel) {
                $attributeData[$optionKey]['value'][$prefix][$storeLabel->getStoreId()]
                    = $storeLabel->getLabel();
                if ($isSwatch === self::SWATCH_TEXT) {
                    $attributeData[$swatchKey]['value'][$prefix][$storeLabel->getStoreId()]
                        = $storeLabel->getLabel();
                }
            }
            $attribute->addData($attributeData);
        }

        return [$entityType, $attributeCode, $option];
    }

    /**
     * @param $attribute
     *
     * @return null|string
     */
    protected function isSwatch($attribute)
    {
        if ($this->swatchHelper->isVisualSwatch($attribute)) {
            return self::SWATCH_VISUAL;
        } elseif ($this->swatchHelper->isTextSwatch($attribute)) {
            return self::SWATCH_TEXT;
        }
        return null;
    }

    /**
     * @param $attributeId
     * @param $optionKey
     *
     * @return array
     */
    protected function prepareAttributeDataForNewOption($attributeId, $optionKey)
    {
        $options       = $this->getOptionsByAttributeIdWithSortOrder($attributeId);
        $attributeData = $this->getOptionsForSwatch($options, $optionKey);
        return $attributeData;
    }

    /**
     * @param []     $options
     * @param string $optionKey
     *
     * @return array
     */
    protected function getOptionsForSwatch($options, $optionKey)
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
     * @param $optionId
     *
     * @return array
     */
    protected function getStoreLabels($optionId)
    {
        $optionCollection = $this->optionCollection->create();
        $connection                  = $optionCollection->getConnection();
        $optionValueTable     = $optionCollection->getTable('eav_attribute_option_value');
        $select = $connection->select()->from(['eaov' => $optionValueTable], [])->where(
            'option_id = ?',
            $optionId
        )->columns(['store_id', 'value']);

        return $connection->fetchPairs($select);
    }

    /**
     * @param $attributeId
     *
     * @return array
     */
    protected function getOptionsByAttributeIdWithSortOrder($attributeId)
    {
        $optionCollection = $this->optionCollection->create();
        $connection                  = $optionCollection->getConnection();
        $optionTable          = $optionCollection->getTable('eav_attribute_option');
        $select = $connection->select()->from(
            ['eao' => $optionTable],
            ['option_id', 'sort_order']
        )->where('eao.attribute_Id = ? ', $attributeId)->order('eao.sort_order ASC');

        return $connection->fetchCol($select);
    }
}

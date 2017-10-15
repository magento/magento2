<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Swatches\Plugin\Catalog;

use Magento\Eav\Model\Entity\Attribute\OptionManagement;
use Magento\Eav\Model\Config;
use Magento\Catalog\Model\Product;
use Magento\Swatches\Helper\Data as SwatchHelper;
use Magento\Eav\Api\Data\AttributeOptionInterface;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory as OptionCollection;

class AddSwatchDataToAddOption
{
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
    protected $attrOptionCollectionFactory;

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
        $this->attrOptionCollectionFactory = $attrOptionCollectionFactory;
    }

    /**
     * @param OptionManagement         $subject
     * @param int                      $entityType
     * @param string                   $attributeCode
     * @param AttributeOptionInterface $option
     *
     * @return []
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function beforeAdd(OptionManagement $subject, $entityType, $attributeCode, $option)
    {
        $attribute = $this->eavConfig->getAttribute(Product::ENTITY, $attributeCode);

        $isSwatch = false;
        if ($this->swatchHelper->isVisualSwatch($attribute)) {
            $isSwatch  = true;
            $optionKey = 'optionvisual';
            $swatchKey = 'swatchvisual';
        } elseif ($this->swatchHelper->isTextSwatch($attribute)) {
            $isSwatch  = true;
            $optionKey = 'optiontext';
            $swatchKey = 'swatchtext';
        }

        if ($isSwatch && $attribute->getData($optionKey) === null && $attribute->getData($swatchKey) === null) {
            $optionId    = $option->getValue();
            $optionOrder = $option->getSortOrder();

            $prefix = '';
            if ($optionId === '') {
                $prefix        = 'option_';
                $options       = $this->getOptionsByAttributeIdWithSortOrder($attribute->getAttributeId());
                $attributeData = $this->getOptionsForSwatch($options, $optionKey);
                $optionId      = count($attributeData[$optionKey]['value']);
                if ($optionOrder === null) {
                    $optionOrder = $optionId + 1;
                }
                $option->setValue($prefix . $optionId);
            }


            $storeLabels                                              = $option->getStoreLabels();
            $attributeData[$optionKey]['delete'][$prefix . $optionId] = '';
            $attributeData[$optionKey]['order'][$prefix . $optionId]  = $optionOrder;
            if ($swatchKey === 'swatchvisual') {
                $attributeData[$swatchKey]['value'][$prefix . $optionId] = '';
            }
            foreach ($storeLabels as $storeLabel) {
                $attributeData[$optionKey]['value'][$prefix . $optionId][$storeLabel->getStoreId()]
                    = $storeLabel->getLabel();
                if ($swatchKey === 'swatchtext') {
                    $attributeData[$swatchKey]['value'][$prefix . $optionId][$storeLabel->getStoreId()]
                        = $storeLabel->getLabel();
                }
            }
            $attribute->addData($attributeData);
        }

        return [$entityType, $attributeCode, $option];
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
        $attrOptionCollectionFactory = $this->attrOptionCollectionFactory->create();
        $connection                  = $attrOptionCollectionFactory->getConnection();
        $eavAttributeOptionValue     = $attrOptionCollectionFactory->getTable('eav_attribute_option_value');
        $select = $connection->select()->from(['eaov' => $eavAttributeOptionValue], [])->where(
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
        $attrOptionCollectionFactory = $this->attrOptionCollectionFactory->create();
        $connection                  = $attrOptionCollectionFactory->getConnection();
        $eavAttributeOption          = $attrOptionCollectionFactory->getTable('eav_attribute_option');
        $select = $connection->select()->from(
            ['eao' => $eavAttributeOption],
            ['option_id', 'sort_order']
        )->where('eao.attribute_Id = ? ', $attributeId)->order('eao.sort_order ASC');

        return $connection->fetchCol($select);
    }
}
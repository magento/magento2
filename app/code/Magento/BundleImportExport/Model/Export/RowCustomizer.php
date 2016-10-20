<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\BundleImportExport\Model\Export;

use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\CatalogImportExport\Model\Export\RowCustomizerInterface;
use Magento\CatalogImportExport\Model\Import\Product as ImportProductModel;
use Magento\Bundle\Model\ResourceModel\Selection\Collection as SelectionCollection;
use Magento\ImportExport\Model\Import as ImportModel;

/**
 * Class RowCustomizer
 */
class RowCustomizer implements RowCustomizerInterface
{
    const BUNDLE_PRICE_TYPE_COL = 'bundle_price_type';

    const BUNDLE_SKU_TYPE_COL = 'bundle_sku_type';

    const BUNDLE_PRICE_VIEW_COL = 'bundle_price_view';

    const BUNDLE_WEIGHT_TYPE_COL = 'bundle_weight_type';

    const BUNDLE_VALUES_COL = 'bundle_values';

    const VALUE_FIXED = 'fixed';

    const VALUE_DYNAMIC = 'dynamic';

    const VALUE_PERCENT = 'percent';

    const VALUE_PRICE_RANGE = 'Price range';

    const VALUE_AS_LOW_AS = 'As low as';

    /**
     * Mapping for bundle types
     *
     * @var array
     */
    protected $typeMapping = [
        '0' => self::VALUE_DYNAMIC,
        '1' => self::VALUE_FIXED
    ];

    /**
     * Mapping for price views
     *
     * @var array
     */
    protected $priceViewMapping = [
        '0' => self::VALUE_PRICE_RANGE,
        '1' => self::VALUE_AS_LOW_AS
    ];

    /**
     * Mapping for price types
     *
     * @var array
     */
    protected $priceTypeMapping = [
        '0' => self::VALUE_FIXED,
        '1' => self::VALUE_PERCENT
    ];

    /**
     * Bundle product columns
     *
     * @var array
     */
    protected $bundleColumns = [
        self::BUNDLE_PRICE_TYPE_COL,
        self::BUNDLE_SKU_TYPE_COL,
        self::BUNDLE_PRICE_VIEW_COL,
        self::BUNDLE_WEIGHT_TYPE_COL,
        self::BUNDLE_VALUES_COL
    ];

    /**
     * Product's bundle data
     *
     * @var array
     */
    protected $bundleData = [];

    /**
     * Prepare data for export
     *
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     * @param int[] $productIds
     * @return $this
     */
    public function prepareData($collection, $productIds)
    {
        $productCollection = clone $collection;
        $productCollection->addAttributeToFilter(
            'entity_id',
            ['in' => $productIds]
        )->addAttributeToFilter(
            'type_id',
            ['eq' => \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE]
        );

        return $this->populateBundleData($productCollection);
    }

    /**
     * Set headers columns
     *
     * @param array $columns
     * @return array
     */
    public function addHeaderColumns($columns)
    {
        $columns = array_merge($columns, $this->bundleColumns);

        return $columns;
    }

    /**
     * Add data for export
     *
     * @param array $dataRow
     * @param int $productId
     * @return array
     */
    public function addData($dataRow, $productId)
    {
        if (!empty($this->bundleData[$productId])) {
            $dataRow = array_merge($this->cleanNotBundleAdditionalAttributes($dataRow), $this->bundleData[$productId]);
        }

        return $dataRow;
    }

    /**
     * Calculate the largest links block
     *
     * @param array $additionalRowsCount
     * @param int $productId
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getAdditionalRowsCount($additionalRowsCount, $productId)
    {
        return $additionalRowsCount;
    }

    /**
     * Populate bundle product data
     *
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     * @return $this
     */
    protected function populateBundleData($collection)
    {
        foreach ($collection as $product) {
            $id = $product->getId();
            $this->bundleData[$id][self::BUNDLE_PRICE_TYPE_COL] = $this->getTypeValue($product->getPriceType());
            $this->bundleData[$id][self::BUNDLE_SKU_TYPE_COL] = $this->getTypeValue($product->getSkuType());
            $this->bundleData[$id][self::BUNDLE_PRICE_VIEW_COL] = $this->getPriceViewValue($product->getPriceView());
            $this->bundleData[$id][self::BUNDLE_WEIGHT_TYPE_COL] = $this->getTypeValue($product->getWeightType());
            $this->bundleData[$id][self::BUNDLE_VALUES_COL] = $this->getFormattedBundleOptionValues($product);
        }

        return $this;
    }

    /**
     * Retrieve formatted bundle options
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    protected function getFormattedBundleOptionValues($product)
    {
        /** @var \Magento\Bundle\Model\ResourceModel\Option\Collection $optionsCollection */
        $optionsCollection = $product->getTypeInstance()
            ->getOptionsCollection($product)
            ->setOrder('position', Collection::SORT_ORDER_ASC);

        $bundleData = '';
        foreach ($optionsCollection as $option) {
            $bundleData .= $this->getFormattedBundleSelections(
                $this->getFormattedOptionValues($option),
                $product->getTypeInstance()
                    ->getSelectionsCollection([$option->getId()], $product)
                    ->setOrder('position', Collection::SORT_ORDER_ASC)
            );
        }

        return rtrim($bundleData, ImportProductModel::PSEUDO_MULTI_LINE_SEPARATOR);
    }

    /**
     * Retrieve formatted bundle selections
     *
     * @param string $optionValues
     * @param SelectionCollection $selections
     * @return string
     */
    protected function getFormattedBundleSelections($optionValues, SelectionCollection $selections)
    {
        $bundleData = '';
        $selections->addAttributeToSort('position');
        foreach ($selections as $selection) {
            $selectionData = [
                'sku' => $selection->getSku(),
                'price' => $selection->getSelectionPriceValue(),
                'default' => $selection->getIsDefault(),
                'default_qty' => $selection->getSelectionQty(),
                'price_type' => $this->getPriceTypeValue($selection->getSelectionPriceType())
            ];
            $bundleData .= $optionValues
                . ImportModel::DEFAULT_GLOBAL_MULTI_VALUE_SEPARATOR
                . implode(
                    ImportModel::DEFAULT_GLOBAL_MULTI_VALUE_SEPARATOR,
                    array_map(
                        function ($value, $key) {
                            return $key . ImportProductModel::PAIR_NAME_VALUE_SEPARATOR . $value;
                        },
                        $selectionData,
                        array_keys($selectionData)
                    )
                )
                . ImportProductModel::PSEUDO_MULTI_LINE_SEPARATOR;
        }

        return $bundleData;
    }

    /**
     * Retrieve option value of bundle product
     *
     * @param \Magento\Bundle\Model\Option $option
     * @return string
     */
    protected function getFormattedOptionValues($option)
    {
        return 'name' . ImportProductModel::PAIR_NAME_VALUE_SEPARATOR
        . $option->getTitle() . ImportModel::DEFAULT_GLOBAL_MULTI_VALUE_SEPARATOR
        . 'type' . ImportProductModel::PAIR_NAME_VALUE_SEPARATOR
        . $option->getType() . ImportModel::DEFAULT_GLOBAL_MULTI_VALUE_SEPARATOR
        . 'required' . ImportProductModel::PAIR_NAME_VALUE_SEPARATOR
        . $option->getRequired();
    }

    /**
     * Retrieve bundle type value by code
     *
     * @param string $type
     * @return string
     */
    protected function getTypeValue($type)
    {
        return isset($this->typeMapping[$type]) ? $this->typeMapping[$type] : self::VALUE_DYNAMIC;
    }

    /**
     * Retrieve bundle price view value by code
     *
     * @param string $type
     * @return string
     */
    protected function getPriceViewValue($type)
    {
        return isset($this->priceViewMapping[$type]) ? $this->priceViewMapping[$type] : self::VALUE_PRICE_RANGE;
    }

    /**
     * Retrieve bundle price type value by code
     *
     * @param string $type
     * @return string
     */
    protected function getPriceTypeValue($type)
    {
        return isset($this->priceTypeMapping[$type]) ? $this->priceTypeMapping[$type] : null;
    }

    /**
     * Remove bundle specified additional attributes as now they are stored in specified columns
     *
     * @param array $dataRow
     * @return array
     */
    protected function cleanNotBundleAdditionalAttributes($dataRow)
    {
        if (!empty($dataRow['additional_attributes'])) {
            $additionalAttributes = $this->parseAdditionalAttributes($dataRow['additional_attributes']);
            $dataRow['additional_attributes'] = $this->getNotBundleAttributes($additionalAttributes);
        }

        return $dataRow;
    }

    /**
     * Retrieve not bundle additional attributes
     *
     * @param array $additionalAttributes
     * @return string
     */
    protected function getNotBundleAttributes($additionalAttributes)
    {
        $filteredAttributes = [];
        foreach ($additionalAttributes as $code => $value) {
            if (!in_array('bundle_' . $code, $this->bundleColumns)) {
                $filteredAttributes[] = $code . ImportProductModel::PAIR_NAME_VALUE_SEPARATOR . $value;
            }
        }
        return implode(ImportModel::DEFAULT_GLOBAL_MULTI_VALUE_SEPARATOR, $filteredAttributes);
    }

    /**
     * Retrieves additional attributes as array code=>value.
     *
     * @param string $additionalAttributes
     * @return array
     */
    private function parseAdditionalAttributes($additionalAttributes)
    {
        $attributeNameValuePairs = explode(ImportModel::DEFAULT_GLOBAL_MULTI_VALUE_SEPARATOR, $additionalAttributes);
        $preparedAttributes = [];
        $code = '';
        foreach ($attributeNameValuePairs as $attributeData) {
            //process case when attribute has ImportModel::DEFAULT_GLOBAL_MULTI_VALUE_SEPARATOR inside its value
            if (strpos($attributeData, ImportProductModel::PAIR_NAME_VALUE_SEPARATOR) === false && $code) {
                $preparedAttributes[$code] .= ImportModel::DEFAULT_GLOBAL_MULTI_VALUE_SEPARATOR . $attributeData;
            } else {
                list($code, $value) = explode(ImportProductModel::PAIR_NAME_VALUE_SEPARATOR, $attributeData, 2);
                $preparedAttributes[$code] = $value;
            }
        }
        return $preparedAttributes;
    }
}

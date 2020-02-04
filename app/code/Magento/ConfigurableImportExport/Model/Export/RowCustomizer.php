<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableImportExport\Model\Export;

use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\CatalogImportExport\Model\Export\RowCustomizerInterface;
use Magento\CatalogImportExport\Model\Import\Product as ImportProduct;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableProductType;
use Magento\ImportExport\Model\Import;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Customizes output during export
 */
class RowCustomizer implements RowCustomizerInterface
{
    /**
     * Header column for Configurable Product variations
     */
    const CONFIGURABLE_VARIATIONS_COLUMN = 'configurable_variations';

    /**
     * Header column for Configurable Product variation labels
     */
    const CONFIGURABLE_VARIATIONS_LABELS_COLUMN = 'configurable_variation_labels';

    /**
     * @var array
     */
    protected $configurableData = [];

    /**
     * @var string[]
     */
    private $configurableColumns = [
        self::CONFIGURABLE_VARIATIONS_COLUMN,
        self::CONFIGURABLE_VARIATIONS_LABELS_COLUMN
    ];

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(StoreManagerInterface $storeManager)
    {
        $this->storeManager = $storeManager;
    }

    /**
     * Prepare configurable data for export
     *
     * @param ProductCollection $collection
     * @param int[] $productIds
     * @return void
     */
    public function prepareData($collection, $productIds)
    {
        $productCollection = clone $collection;
        $productCollection->addAttributeToFilter('entity_id', ['in' => $productIds])
            ->addAttributeToFilter('type_id', ['eq' => ConfigurableProductType::TYPE_CODE]);

        // set global scope during export
        $this->storeManager->setCurrentStore(Store::DEFAULT_STORE_ID);

        while ($product = $productCollection->fetchItem()) {
            $productAttributesOptions = $product->getTypeInstance()->getConfigurableOptions($product);
            $this->configurableData[$product->getId()] = [];
            $variations = [];
            $variationsLabels = [];

            foreach ($productAttributesOptions as $productAttributeOption) {
                foreach ($productAttributeOption as $optValues) {
                    $variations[$optValues['sku']][] = $optValues['attribute_code'] . '=' . $optValues['option_title'];

                    if (!empty($optValues['super_attribute_label'])) {
                        $variationsLabels[$optValues['attribute_code']] = $optValues['attribute_code'] . '='
                            . $optValues['super_attribute_label'];
                    }
                }
            }

            foreach ($variations as $sku => $values) {
                $variations[$sku] = 'sku=' . $sku . Import::DEFAULT_GLOBAL_MULTI_VALUE_SEPARATOR
                    . implode(Import::DEFAULT_GLOBAL_MULTI_VALUE_SEPARATOR, $values);
            }

            $this->configurableData[$product->getId()] = [
                self::CONFIGURABLE_VARIATIONS_COLUMN => implode(
                    ImportProduct::PSEUDO_MULTI_LINE_SEPARATOR,
                    $variations
                ),
                self::CONFIGURABLE_VARIATIONS_LABELS_COLUMN => implode(
                    Import::DEFAULT_GLOBAL_MULTI_VALUE_SEPARATOR,
                    $variationsLabels
                )
            ];
        }
    }

    /**
     * Set headers columns
     *
     * @param array $columns
     * @return array
     */
    public function addHeaderColumns($columns)
    {
        return array_merge($columns, $this->configurableColumns);
    }

    /**
     * Add configurable data for export
     *
     * @param array $dataRow
     * @param int $productId
     * @return array
     */
    public function addData($dataRow, $productId)
    {
        if (!empty($this->configurableData[$productId])) {
            $dataRow = array_merge($dataRow, $this->configurableData[$productId]);
        }
        return $dataRow;
    }

    /**
     * Calculate the largest links block
     *
     * @param array $additionalRowsCount
     * @param int $productId
     * @return array
     */
    public function getAdditionalRowsCount($additionalRowsCount, $productId)
    {
        if (!empty($this->configurableData[$productId])) {
            $additionalRowsCount = max($additionalRowsCount, count($this->configurableData[$productId]));
        }
        return $additionalRowsCount;
    }
}

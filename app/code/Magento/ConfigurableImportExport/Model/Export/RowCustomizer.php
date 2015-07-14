<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableImportExport\Model\Export;

use Magento\CatalogImportExport\Model\Export\RowCustomizerInterface;
use \Magento\CatalogImportExport\Model\Import\Product as ImportProduct;

class RowCustomizer implements RowCustomizerInterface
{
    /**
     * @var array
     */
    protected $configurableData = [];

    /**
     * Prepare configurable data for export
     *
     * @param \Magento\Catalog\Model\Resource\Product\Collection $collection
     * @param int $productIds
     * @return void
     */
    public function prepareData($collection, $productIds)
    {
        $collection->addAttributeToFilter(
            'entity_id',
            ['in' => $productIds]
        )->addAttributeToFilter(
            'type_id',
            ['eq' => \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE]
        );

        while ($product = $collection->fetchItem()) {
            $productAttributesOptions = $product->getTypeInstance()->getConfigurableOptions($product);

            foreach ($productAttributesOptions as $productAttributeOption) {
                $this->configurableData[$product->getId()] = [];
                $variations = [];
                $variationsLabels = [];

                foreach ($productAttributeOption as $optValues) {
                    $variations[$optValues['sku']][] =
                        $optValues['attribute_code'] . '=' . $optValues['option_title'];
                    if (!empty($optValues['super_attribute_label'])) {
                        $variationsLabels[$optValues['attribute_code']] =
                            $optValues['attribute_code'] . '=' . $optValues['super_attribute_label'];
                    }
                }

                foreach ($variations as $sku => $values) {
                    $variations[$sku] =
                        'sku=' . $sku . ImportProduct::DEFAULT_GLOBAL_MULTI_VALUE_SEPARATOR
                        . implode(ImportProduct::DEFAULT_GLOBAL_MULTI_VALUE_SEPARATOR, $values);
                }
                $variations = implode(ImportProduct::PSEUDO_MULTI_LINE_SEPARATOR, $variations);
                $variationsLabels = implode(ImportProduct::DEFAULT_GLOBAL_MULTI_VALUE_SEPARATOR, $variationsLabels);

                $this->configurableData[$product->getId()] = [
                    'configurable_variations' => $variations,
                    'configurable_variation_labels' => $variationsLabels,
                ];
            }
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
        // have we merge configurable products data
        if (!empty($this->configurableData)) {
            $columns = array_merge(
                $columns,
                [
                    'configurable_variations',
                    'configurable_variation_labels',
                ]
            );
        }
        return $columns;
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

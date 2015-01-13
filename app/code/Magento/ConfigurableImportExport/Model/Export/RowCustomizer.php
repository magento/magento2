<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableImportExport\Model\Export;

use Magento\CatalogImportExport\Model\Export\RowCustomizerInterface;

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
                foreach ($productAttributeOption as $optionValues) {
                    $priceType = $optionValues['pricing_is_percent'] ? '%' : '';
                    $this->configurableData[$product->getId()][] = [
                        '_super_products_sku' => $optionValues['sku'],
                        '_super_attribute_code' => $optionValues['attribute_code'],
                        '_super_attribute_option' => $optionValues['option_title'],
                        '_super_attribute_price_corr' => $optionValues['pricing_value'] . $priceType,
                    ];
                }
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
                    '_super_products_sku',
                    '_super_attribute_code',
                    '_super_attribute_option',
                    '_super_attribute_price_corr'
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
            $dataRow = array_merge($dataRow, array_shift($this->configurableData[$productId]));
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

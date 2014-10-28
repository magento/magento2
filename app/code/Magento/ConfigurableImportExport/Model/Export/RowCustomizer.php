<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\ConfigurableImportExport\Model\Export;

use \Magento\CatalogImportExport\Model\Export\RowCustomizerInterface;

class RowCustomizer implements RowCustomizerInterface
{
    /**
     * @var array
     */
    protected $configurableData = array();

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
            array('in' => $productIds)
        )->addAttributeToFilter(
            'type_id',
            array('eq' => \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE)
        );

        while ($product = $collection->fetchItem()) {
            $productAttributesOptions = $product->getTypeInstance()->getConfigurableOptions($product);

            foreach ($productAttributesOptions as $productAttributeOption) {
                $this->configurableData[$product->getId()] = array();
                foreach ($productAttributeOption as $optionValues) {
                    $priceType = $optionValues['pricing_is_percent'] ? '%' : '';
                    $this->configurableData[$product->getId()][] = array(
                        '_super_products_sku' => $optionValues['sku'],
                        '_super_attribute_code' => $optionValues['attribute_code'],
                        '_super_attribute_option' => $optionValues['option_title'],
                        '_super_attribute_price_corr' => $optionValues['pricing_value'] . $priceType
                    );
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
                array(
                    '_super_products_sku',
                    '_super_attribute_code',
                    '_super_attribute_option',
                    '_super_attribute_price_corr'
                )
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

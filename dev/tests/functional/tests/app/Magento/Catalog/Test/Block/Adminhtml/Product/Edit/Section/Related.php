<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Section;

use Magento\Mtf\Client\Element\SimpleElement;
use Magento\Ui\Test\Block\Adminhtml\Section;
use Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Section\Related\Grid;

/**
 * Base class for related, crosssell, upsell products.
 */
class Related extends Section
{
    /**
     * Locator for 'Add Selected Products' button
     *
     * @var string
     */
    protected $addProducts = '.action-primary[data-role="action"]';

    /**
     * Select related products.
     *
     * @param array $data
     * @param SimpleElement|null $element
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setFieldsData(array $data, SimpleElement $element = null)
    {
        $relatedProducts = array_keys($data);
        foreach ($relatedProducts as $relatedProduct) {
            $relatedTypeUnderscore = substr($relatedProduct, 0, strpos($relatedProduct, '_products'));
            $relatedType = str_replace('_', '', $relatedTypeUnderscore);
            $this->_rootElement->find('[data-index="button_' . $relatedType . '"]')->click();
            $context = '';

            if (isset($data[$relatedTypeUnderscore . '_products']['value'])) {
                $context = $this->browser->find('.product_form_product_form_related_' . $relatedType . '_modal');
                $relatedBlock = $this->getRelatedGrid($context);

                foreach ($data[$relatedTypeUnderscore . '_products']['value'] as $product) {
                    $relatedBlock->searchAndSelect(['sku' => $product['sku']]);
                }
            }
            $context->find($this->addProducts)->click();
        }

        return $this;
    }

    /**
     * Get data of section.
     *
     * @param array|null $fields
     * @param SimpleElement|null $element
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getFieldsData($fields = null, SimpleElement $element = null)
    {
        $relatedProducts = array_keys($fields);
        $data = [];
        foreach ($relatedProducts as $relatedProduct) {
            $relatedTypeUnderscore = substr($relatedProduct, 0, strpos($relatedProduct, '_products'));
            $relatedType = str_replace('_', '', $relatedTypeUnderscore);
            $context = $this->browser->find('[data-index="' . $relatedType . '"]');
            $relatedBlock = $this->getRelatedGrid($context);
            $columns = ['id', 'name', 'sku'];
            $relatedProducts = $relatedBlock->getRowsData($columns);
            $data = [$relatedProduct => $relatedProducts];
        }

        return $data;
    }

    /**
     * Return related products grid.
     *
     * @param SimpleElement|null $element
     * @return Grid
     */
    protected function getRelatedGrid(SimpleElement $element = null)
    {
        $element = $element ?: $this->_rootElement;
        return $this->blockFactory->create(
            \Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Section\Related\Grid::class,
            ['element' => $element]
        );
    }
}

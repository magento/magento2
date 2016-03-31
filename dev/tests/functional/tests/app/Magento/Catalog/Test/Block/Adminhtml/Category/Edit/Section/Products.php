<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Block\Adminhtml\Category\Edit\Section;

use Magento\Ui\Test\Block\Adminhtml\Section;
use Magento\Mtf\Client\Element\SimpleElement;

/**
 * Category Products section.
 */
class Products extends Section
{
    /**
     * An element locator which allows to select entities in grid.
     *
     * @var string
     */
    protected $selectItem = 'tbody tr .col-in_category';

    /**
     * Product grid locator.
     *
     * @var string
     */
    protected $productGrid = '#catalog_category_products';

    /**
     * Fill category products.
     *
     * @param array $fields
     * @param SimpleElement|null $element
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setFieldsData(array $fields, SimpleElement $element = null)
    {
        if (!isset($fields['category_products'])) {
            return;
        }
        foreach ($fields['category_products']['source']->getData() as $productName) {
            $this->getProductGrid()->searchAndSelect(['name' => $productName]);
        }
    }

    /**
     * Get data of the Category Products section.
     *
     * @param array|null $fields
     * @param SimpleElement|null $element
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getFieldsData($fields = null, SimpleElement $element = null)
    {
        $data = $this->dataMapping($fields);
        $result = [];

        if (isset($data['category_products'])) {
            $this->getProductGrid()->search(['in_category' => 'Yes']);
            $rows = $this->getProductGrid()->getRowsData(['name']);

            foreach ($rows as $row) {
                $result['category_products'][] = $row['name'];
            }
        }

        return $result;
    }

    /**
     * Returns product grid.
     *
     * @return \Magento\Catalog\Test\Block\Adminhtml\Category\Edit\Section\ProductGrid
     */
    public function getProductGrid()
    {
        return $this->blockFactory->create(
            'Magento\Catalog\Test\Block\Adminhtml\Category\Edit\Section\ProductGrid',
            ['element' => $this->_rootElement->find($this->productGrid)]
        );
    }
}

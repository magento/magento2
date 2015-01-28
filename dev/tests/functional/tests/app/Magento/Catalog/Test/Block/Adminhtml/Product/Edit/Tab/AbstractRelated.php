<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Tab;

use Magento\Backend\Test\Block\Widget\Tab;
use Magento\Backend\Test\Block\Widget\Grid;
use Magento\Mtf\Client\Element\SimpleElement;

/**
 * Class AbstractRelated
 * Base class for related products tab
 */
abstract class AbstractRelated extends Tab
{
    /**
     * Type related products
     *
     * @var string
     */
    protected $relatedType = '';

    /**
     * Select related products
     *
     * @param array $data
     * @param SimpleElement|null $element
     * @return $this
     */
    public function fillFormTab(array $data, SimpleElement $element = null)
    {
        if (isset($data[$this->relatedType]['value'])) {
            $context = $element ? $element : $this->_rootElement;
            $relatedBlock = $this->getRelatedGrid($context);

            foreach ($data[$this->relatedType]['value'] as $product) {
                $relatedBlock->searchAndSelect(['sku' => $product['sku']]);
            }
        }

        return $this;
    }

    /**
     * Get data of tab
     *
     * @param array|null $fields
     * @param SimpleElement|null $element
     * @return array
     */
    public function getDataFormTab($fields = null, SimpleElement $element = null)
    {
        $relatedBlock = $this->getRelatedGrid($element);
        $columns = [
            'entity_id',
            'name',
            'sku',
        ];
        $relatedProducts = $relatedBlock->getRowsData($columns);

        return [$this->relatedType => $relatedProducts];
    }

    /**
     * Return related products grid
     *
     * @param SimpleElement $element
     * @return Grid
     */
    abstract protected function getRelatedGrid(SimpleElement $element = null);
}

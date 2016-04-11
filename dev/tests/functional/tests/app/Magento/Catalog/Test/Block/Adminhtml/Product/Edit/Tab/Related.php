<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Tab;

use Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Tab\Related\Grid as RelatedGrid;
use Magento\Mtf\Client\Element\SimpleElement;

/**
 * Class Related
 * Related Tab
 */
class Related extends AbstractRelated
{
    /**
     * Related products type
     *
     * @var string
     */
    protected $relatedType = 'related_products';

    /**
     * Locator for related products grid
     *
     * @var string
     */
    protected $relatedGrid = '#related_product_grid';

    /**
     * Return related products grid
     *
     * @param SimpleElement|null $element [optional]
     * @return RelatedGrid
     */
    protected function getRelatedGrid(SimpleElement $element = null)
    {
        $element = $element ? $element : $this->_rootElement;

        return $this->blockFactory->create(
            '\Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Tab\Related\Grid',
            ['element' => $element->find($this->relatedGrid)]
        );
    }
}

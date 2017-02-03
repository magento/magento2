<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Test\Block\Dashboard\Tab;

use Magento\Mtf\Client\Element;
use Magento\Backend\Test\Block\Widget\Tab;

class Products extends Tab
{
    /**
     * Locator for ordered products grid
     *
     * @var string
     */
    protected $orderedProductsGrid = '#grid_tab_ordered_products_content';

    /**
     * Get bestsellers grid
     *
     * @return \Magento\Backend\Test\Block\Dashboard\Tab\Products\Ordered
     */
    public function getBestsellersGrid()
    {
        return $this->blockFactory->create(
            '\Magento\Backend\Test\Block\Dashboard\Tab\Products\Ordered',
            ['element' => $this->browser->find($this->orderedProductsGrid)]
        );
    }
}

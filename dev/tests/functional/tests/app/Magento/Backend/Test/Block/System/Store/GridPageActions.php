<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Test\Block\System\Store;

use Magento\Backend\Test\Block\GridPageActions as ParentGridPageActions;

/**
 * Class GridPageActions
 * Grid page actions block in Cms Block grid page
 */
class GridPageActions extends ParentGridPageActions
{
    /**
     * Add Store View button
     *
     * @var string
     */
    protected $addStoreViewButton = '#add_store';

    /**
     * Click on Add Store View button
     *
     * @return void
     */
    public function addStoreView()
    {
        $this->_rootElement->find($this->addStoreViewButton)->click();
    }
}

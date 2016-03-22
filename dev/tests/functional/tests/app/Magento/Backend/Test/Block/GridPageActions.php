<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Test\Block;

/**
 * Class GridPageActions
 * Grid page actions block
 */
class GridPageActions extends PageActions
{
    /**
     * "Add New" button
     *
     * @var string
     */
    protected $addNewButton = '#add';

    /**
     * "Create Store" button selector
     *
     * @var string
     */
    protected $createStoreButton = '#add_group';

    /**
     * Click on "Add New" button
     *
     * @return void
     */
    public function addNew()
    {
        $this->_rootElement->find($this->addNewButton)->click();
    }

    /**
     * Click on "Create Store" button
     *
     * @return void
     */
    public function createStoreGroup()
    {
        $this->_rootElement->find($this->createStoreButton)->click();
    }
}

<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Block\Adminhtml\Edit;

use Magento\Backend\Test\Block\FormPageActions as ParentFormPageActions;

/**
 * Class FormPageActions
 * Form page actions block for customer page
 */
class FormPageActions extends ParentFormPageActions
{
    /**
     * "Create Order" button
     *
     * @var string
     */
    protected $createOrderButton = '#order';

    /**
     * Click on "Create Order" button
     *
     * @return void
     */
    public function createOrder()
    {
        $this->_rootElement->find($this->createOrderButton)->click();
    }

    /**
     * Wait for User before click on any Button which calls JS validation on correspondent form.
     * See details in MAGETWO-31121.
     *
     * @return void
     */
    protected function waitBeforeClick()
    {
        sleep(0.2);
    }
}

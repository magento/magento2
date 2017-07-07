<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Block\Adminhtml\Edit;

use Magento\Backend\Test\Block\FormPageActions as ParentFormPageActions;

/**
 * Form page actions block for customer page.
 */
class FormPageActions extends ParentFormPageActions
{
    /**
     * "Create Order" button.
     *
     * @var string
     */
    protected $createOrderButton = '#order';

    /**
     * "Manage Shopping Cart" button.
     *
     * @var string
     */
    protected $manageShoppingCartButton = '#manage_quote';

    /**
     * Click on "Create Order" button.
     *
     * @return void
     */
    public function createOrder()
    {
        $this->_rootElement->find($this->createOrderButton)->click();
    }

    /**
     * Click on "Manage Shopping Cart" button.
     *
     * @return void
     */
    public function manageShoppingCart()
    {
        $this->_rootElement->find($this->manageShoppingCartButton)->click();
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

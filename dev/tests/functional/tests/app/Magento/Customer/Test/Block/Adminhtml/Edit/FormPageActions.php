<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
}

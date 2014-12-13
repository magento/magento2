<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\User\Test\Block\Adminhtml\User\Edit;

use Magento\Backend\Test\Block\FormPageActions;

/**
 * Class PageActions
 * User page actions on user edit page.
 */
class PageActions extends FormPageActions
{
    /**
     * 'Force Sign-In' button selector.
     *
     * @var string
     */
    protected $forceSignIn = '#invalidate';

    /**
     * Click on 'Force Sign-In' button.
     *
     * @return void
     */
    public function forceSignIn()
    {
        $this->_rootElement->find($this->forceSignIn)->click();
        $this->_rootElement->acceptAlert();
    }
}

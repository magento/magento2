<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\CheckoutAgreements\Controller\Adminhtml\Agreement;

class NewAction extends \Magento\CheckoutAgreements\Controller\Adminhtml\Agreement
{
    /**
     * @return void
     */
    public function execute()
    {
        $this->_forward('edit');
    }
}

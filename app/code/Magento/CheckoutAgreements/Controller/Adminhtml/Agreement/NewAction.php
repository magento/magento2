<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CheckoutAgreements\Controller\Adminhtml\Agreement;

/**
 * Class \Magento\CheckoutAgreements\Controller\Adminhtml\Agreement\NewAction
 *
 */
class NewAction extends \Magento\CheckoutAgreements\Controller\Adminhtml\Agreement
{
    /**
     * @return void
     * @codeCoverageIgnore
     */
    public function execute()
    {
        $this->_forward('edit');
    }
}

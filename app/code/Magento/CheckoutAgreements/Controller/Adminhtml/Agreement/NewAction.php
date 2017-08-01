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
 * @since 2.0.0
 */
class NewAction extends \Magento\CheckoutAgreements\Controller\Adminhtml\Agreement
{
    /**
     * @return void
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function execute()
    {
        $this->_forward('edit');
    }
}

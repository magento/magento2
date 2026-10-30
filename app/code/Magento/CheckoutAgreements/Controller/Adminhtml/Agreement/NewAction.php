<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\CheckoutAgreements\Controller\Adminhtml\Agreement;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;

class NewAction extends \Magento\CheckoutAgreements\Controller\Adminhtml\Agreement implements HttpGetActionInterface
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

<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Authorizenet\Controller\Directpost\Payment;

class BackendResponse extends \Magento\Authorizenet\Controller\Directpost\Payment
{
    /**
     * Response action.
     * Action for Authorize.net SIM Relay Request.
     *
     * @return void
     */
    public function execute()
    {
        $this->_responseAction('adminhtml');
    }
}

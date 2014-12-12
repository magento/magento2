<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Authorizenet\Controller\Directpost\Payment;

class Response extends \Magento\Authorizenet\Controller\Directpost\Payment
{
    /**
     * Response action.
     * Action for Authorize.net SIM Relay Request.
     *
     * @return void
     */
    public function execute()
    {
        $this->_responseAction($this->_objectManager->get('Magento\Authorizenet\Helper\Data'));
    }
}

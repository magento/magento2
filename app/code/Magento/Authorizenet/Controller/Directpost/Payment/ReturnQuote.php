<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Authorizenet\Controller\Directpost\Payment;

class ReturnQuote extends \Magento\Authorizenet\Controller\Directpost\Payment
{
    /**
     * Return customer quote by ajax
     *
     * @return void
     */
    public function execute()
    {
        $this->_returnCustomerQuote();
        $this->getResponse()->representJson(
            $this->_objectManager->get('Magento\Framework\Json\Helper\Data')->jsonEncode(['success' => 1])
        );
    }
}

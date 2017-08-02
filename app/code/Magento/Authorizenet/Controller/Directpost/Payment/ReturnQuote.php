<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Authorizenet\Controller\Directpost\Payment;

/**
 * Class \Magento\Authorizenet\Controller\Directpost\Payment\ReturnQuote
 *
 * @since 2.0.0
 */
class ReturnQuote extends \Magento\Authorizenet\Controller\Directpost\Payment
{
    /**
     * Return customer quote by ajax
     *
     * @return void
     * @since 2.0.0
     */
    public function execute()
    {
        $this->_returnCustomerQuote();
        $this->getResponse()->representJson(
            $this->_objectManager->get(\Magento\Framework\Json\Helper\Data::class)->jsonEncode(['success' => 1])
        );
    }
}

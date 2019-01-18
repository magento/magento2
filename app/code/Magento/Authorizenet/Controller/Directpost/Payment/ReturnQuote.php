<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Authorizenet\Controller\Directpost\Payment;

/**
 * Class ReturnQuote
 * @package Magento\Authorizenet\Controller\Directpost\Payment
 * @deprecated 2.3.1 Authorize.net is removing all support for this payment method in July 2019
 */
class ReturnQuote extends \Magento\Authorizenet\Controller\Directpost\Payment
{
    /**
     * Return customer quote by ajax
     *
     * @return void
     * @deprecated
     */
    public function execute()
    {
        $this->_returnCustomerQuote();
        $this->getResponse()->representJson(
            $this->_objectManager->get(\Magento\Framework\Json\Helper\Data::class)->jsonEncode(['success' => 1])
        );
    }
}

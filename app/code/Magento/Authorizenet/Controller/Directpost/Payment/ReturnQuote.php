<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Authorizenet\Controller\Directpost\Payment;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Authorizenet\Controller\Directpost\Payment;

/**
 * Class ReturnQuote
 * @deprecated 100.3.1 Authorize.net is removing all support for this payment method
 */
class ReturnQuote extends Payment implements HttpPostActionInterface, HttpGetActionInterface
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
            $this->_objectManager->get(\Magento\Framework\Json\Helper\Data::class)->jsonEncode(['success' => 1])
        );
    }
}

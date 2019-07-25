<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Authorizenet\Controller\Adminhtml\Authorizenet\Directpost\Payment;

use Magento\Framework\App\Action\HttpPutActionInterface;
use Magento\Sales\Controller\Adminhtml\Order\Create\ConfigureQuoteItems as BaseConfigureQuoteItems;

/**
 * Class ConfigureQuoteItems
 * @deprecated 2.3 Authorize.net is removing all support for this payment method
 */
class ConfigureQuoteItems extends BaseConfigureQuoteItems implements HttpPutActionInterface
{
}

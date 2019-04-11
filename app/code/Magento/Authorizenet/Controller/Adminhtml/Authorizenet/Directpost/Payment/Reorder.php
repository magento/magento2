<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Authorizenet\Controller\Adminhtml\Authorizenet\Directpost\Payment;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Sales\Controller\Adminhtml\Order\Create\Reorder as BaseReorder;

/**
 * Class Reorder
 * @deprecated 2.3.1 Authorize.net is removing all support for this payment method
 */
class Reorder extends BaseReorder implements HttpPostActionInterface
{
}

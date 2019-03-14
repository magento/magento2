<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryShipping\Controller\Adminhtml\Order\Shipment;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Shipping\Controller\Adminhtml\Order\Shipment\NewAction as LegacyNewAction;

class NewAction extends LegacyNewAction implements HttpPostActionInterface
{

}

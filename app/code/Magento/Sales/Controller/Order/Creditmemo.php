<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Order;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Sales\Controller\OrderInterface;

class Creditmemo extends \Magento\Sales\Controller\AbstractController\Creditmemo implements OrderInterface, HttpGetActionInterface
{
}

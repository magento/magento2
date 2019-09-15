<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Controller\Order;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Sales\Controller\OrderInterface;
use Magento\Sales\Controller\AbstractController\Creditmemo as AbstractCreditmemo;

class Creditmemo extends AbstractCreditmemo implements OrderInterface, HttpGetActionInterface
{
}

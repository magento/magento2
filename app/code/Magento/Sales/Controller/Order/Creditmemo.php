<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Sales\Controller\Order;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Sales\Controller\OrderInterface;
use Magento\Sales\Controller\AbstractController\Creditmemo as AbstractCreditmemo;

class Creditmemo extends AbstractCreditmemo implements OrderInterface, HttpGetActionInterface
{
}

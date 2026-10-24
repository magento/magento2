<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Sales\Controller\Order;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Sales\Controller\OrderInterface;

class View extends \Magento\Sales\Controller\AbstractController\View implements OrderInterface, HttpGetActionInterface
{
}

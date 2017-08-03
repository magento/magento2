<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Model\Payflow\Service\Response\Handler;

use Magento\Framework\DataObject;
use Magento\Payment\Model\InfoInterface;

/**
 * Interface HandlerInterface used for error handling of gateway response
 * @package Magento\Paypal\Model\Payflow\Service\Response\Handler
 * @since 2.0.0
 */
interface HandlerInterface
{
    /**
     * Handles response error
     *
     * @param InfoInterface $payment
     * @param Object $response
     * @return void
     * @since 2.0.0
     */
    public function handle(InfoInterface $payment, DataObject $response);
}

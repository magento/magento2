<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Gateway\Response;

/**
 * Interface HandlerInterface
 * @package Magento\Payment\Gateway\Response
 * @api
 * @since 2.0.0
 */
interface HandlerInterface
{
    /**
     * Handles response
     *
     * @param array $handlingSubject
     * @param array $response
     * @return void
     * @since 2.0.0
     */
    public function handle(array $handlingSubject, array $response);
}

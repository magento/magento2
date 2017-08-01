<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Gateway\Config;

/**
 * Interface ValueHandlerInterface
 * @package Magento\Payment\Gateway\Config
 * @api
 * @since 2.0.0
 */
interface ValueHandlerInterface
{
    /**
     * Retrieve method configured value
     *
     * @param array $subject
     * @param int|null $storeId
     *
     * @return mixed
     * @since 2.0.0
     */
    public function handle(array $subject, $storeId = null);
}

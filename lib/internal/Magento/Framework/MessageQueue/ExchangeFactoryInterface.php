<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue;

/**
 * Factory class for @see \Magento\Framework\MessageQueue\ExchangeInterface
 *
 * @api
 * @since 102.0.1
 */
interface ExchangeFactoryInterface
{
    /**
     * Create exchange instance.
     *
     * @param string $connectionName
     * @param array $data
     * @return ExchangeInterface
     * @since 102.0.1
     */
    public function create($connectionName, array $data = []);
}

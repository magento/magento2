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
 */
interface ExchangeFactoryInterface
{
    /**
     * Create exchange instance.
     *
     * @param string $connectionName
     * @param array $data
     * @return ExchangeInterface
     */
    public function create($connectionName, array $data = []);
}

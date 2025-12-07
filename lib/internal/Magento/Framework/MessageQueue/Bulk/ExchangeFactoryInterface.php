<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\MessageQueue\Bulk;

/**
 * Factory class for @see \Magento\Framework\MessageQueue\Bulk\ExchangeInterface
 *
 * @api
 * @since 103.0.0
 */
interface ExchangeFactoryInterface
{
    /**
     * Create exchange instance.
     *
     * @param string $connectionName
     * @param array $data
     * @return ExchangeInterface
     * @throws \LogicException If exchange is not defined for the specified connection type
     *                          or it doesn't implement ExchangeInterface
     * @since 103.0.0
     */
    public function create($connectionName, array $data = []);
}

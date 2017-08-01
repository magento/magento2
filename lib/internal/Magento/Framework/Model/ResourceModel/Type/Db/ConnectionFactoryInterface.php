<?php
/**
 * Connection adapter factory interface
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Model\ResourceModel\Type\Db;

/**
 * Interface \Magento\Framework\Model\ResourceModel\Type\Db\ConnectionFactoryInterface
 *
 * @since 2.0.0
 */
interface ConnectionFactoryInterface
{
    /**
     * Create connection adapter instance
     *
     * @param array $connectionConfig
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     * @throws \InvalidArgumentException
     * @since 2.0.0
     */
    public function create(array $connectionConfig);
}

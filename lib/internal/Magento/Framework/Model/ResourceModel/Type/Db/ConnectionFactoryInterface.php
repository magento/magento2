<?php
/**
 * Connection adapter factory interface
 *
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Model\ResourceModel\Type\Db;

/**
 * Interface \Magento\Framework\Model\ResourceModel\Type\Db\ConnectionFactoryInterface
 *
 * @api
 */
interface ConnectionFactoryInterface
{
    /**
     * Create connection adapter instance
     *
     * @param array $connectionConfig
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     * @throws \InvalidArgumentException
     */
    public function create(array $connectionConfig);
}

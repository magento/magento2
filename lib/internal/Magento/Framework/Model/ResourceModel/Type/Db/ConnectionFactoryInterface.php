<?php
/**
 * Connection adapter factory interface
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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

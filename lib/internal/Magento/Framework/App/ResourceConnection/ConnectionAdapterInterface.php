<?php
/**
 * Connection adapter interface
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\ResourceConnection;

use Magento\Framework\DB\LoggerInterface;

interface ConnectionAdapterInterface
{
    /**
     * Get connection
     *
     * @param LoggerInterface $logger
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     * @throws \InvalidArgumentException
     */
    public function getConnection(LoggerInterface $logger);
}

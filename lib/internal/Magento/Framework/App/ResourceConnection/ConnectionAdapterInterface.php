<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\ResourceConnection;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\LoggerInterface;
use Magento\Framework\DB\SelectFactory;

/**
 * Connection adapter interface
 *
 */
interface ConnectionAdapterInterface
{
    /**
     * Get connection
     *
     * @param LoggerInterface|null $logger
     * @param SelectFactory|null $selectFactory
     * @return AdapterInterface
     * @throws \InvalidArgumentException
     */
    public function getConnection(LoggerInterface $logger = null, SelectFactory $selectFactory = null);
}

<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\ResourceConnection;

use Magento\Framework\DB\LoggerInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\SelectFactory;

/*
 * Connection adapter interface
 */
interface ConnectionAdapterInterface
{
    /**
     * Get connection
     *
     * @param LoggerInterface $logger
     * @param SelectFactory|null $selectFactory
     * @return AdapterInterface
     * @throws \InvalidArgumentException
     */
    public function getConnection(LoggerInterface $logger, SelectFactory $selectFactory = null);
}

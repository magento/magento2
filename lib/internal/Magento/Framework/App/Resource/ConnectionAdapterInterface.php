<?php
/**
 * Connection adapter interface
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Resource;

use Magento\Framework\DB\LoggerInterface;

interface ConnectionAdapterInterface
{
    /**
     * Get connection
     *
     * @param LoggerInterface $logger
     * @return \Magento\Framework\DB\Adapter\AdapterInterface|null
     */
    public function getConnection(LoggerInterface $logger);
}

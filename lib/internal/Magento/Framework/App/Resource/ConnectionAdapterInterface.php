<?php
/**
 * Connection adapter interface
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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

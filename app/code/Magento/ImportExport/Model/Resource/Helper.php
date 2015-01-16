<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * ImportExport MySQL resource helper model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\ImportExport\Model\Resource;

class Helper extends \Magento\Framework\DB\Helper
{
    /**
     * Constants to be used for DB
     */
    const DB_MAX_PACKET_SIZE = 1048576;

    // Maximal packet length by default in MySQL
    const DB_MAX_PACKET_COEFFICIENT = 0.85;

    // The coefficient of useful data from maximum packet length

    /**
     * @param \Magento\Framework\App\Resource $resource
     * @param string $modulePrefix
     */
    public function __construct(\Magento\Framework\App\Resource $resource, $modulePrefix = 'importexport')
    {
        parent::__construct($resource, $modulePrefix);
    }

    /**
     * Returns maximum size of packet, that we can send to DB
     *
     * @return int
     */
    public function getMaxDataSize()
    {
        $maxPacketData = $this->_getReadAdapter()->fetchRow('SHOW VARIABLES LIKE "max_allowed_packet"');
        $maxPacket = empty($maxPacketData['Value']) ? self::DB_MAX_PACKET_SIZE : $maxPacketData['Value'];
        return floor($maxPacket * self::DB_MAX_PACKET_COEFFICIENT);
    }

    /**
     * Returns next autoincrement value for a table
     *
     * @param string $tableName Real table name in DB
     * @return int
     */
    public function getNextAutoincrement($tableName)
    {
        $adapter = $this->_getReadAdapter();
        $entityStatus = $adapter->showTableStatus($tableName);

        if (empty($entityStatus['Auto_increment'])) {
            throw new \Magento\Framework\Model\Exception(__('Cannot get autoincrement value'));
        }
        return $entityStatus['Auto_increment'];
    }
}

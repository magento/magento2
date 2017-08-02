<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Model\ResourceModel;

/**
 * ImportExport MySQL resource helper model
 *
 * @api
 * @since 2.0.0
 */
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
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param string $modulePrefix
     * @since 2.0.0
     */
    public function __construct(\Magento\Framework\App\ResourceConnection $resource, $modulePrefix = 'importexport')
    {
        parent::__construct($resource, $modulePrefix);
    }

    /**
     * Returns maximum size of packet, that we can send to DB
     *
     * @return int
     * @since 2.0.0
     */
    public function getMaxDataSize()
    {
        $maxPacketData = $this->getConnection()->fetchRow('SHOW VARIABLES LIKE "max_allowed_packet"');
        $maxPacket = empty($maxPacketData['Value']) ? self::DB_MAX_PACKET_SIZE : $maxPacketData['Value'];
        return floor($maxPacket * self::DB_MAX_PACKET_COEFFICIENT);
    }

    /**
     * Returns next autoincrement value for a table
     *
     * @param string $tableName Real table name in DB
     * @return int
     * @since 2.0.0
     */
    public function getNextAutoincrement($tableName)
    {
        $connection = $this->getConnection();
        $entityStatus = $connection->showTableStatus($tableName);

        if (empty($entityStatus['Auto_increment'])) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Cannot get autoincrement value'));
        }
        return $entityStatus['Auto_increment'];
    }
}

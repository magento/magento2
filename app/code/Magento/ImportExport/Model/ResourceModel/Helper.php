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
 * @since 100.0.2
 */
class Helper extends \Magento\Framework\DB\Helper
{
    /**
     * Constants to be used for DB
     */
    public const DB_MAX_PACKET_SIZE = 1048576;

    // Maximal packet length by default in MySQL
    public const DB_MAX_PACKET_COEFFICIENT = 0.85;

    // The coefficient of useful data from maximum packet length

    /**
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param string $modulePrefix
     * phpcs:disable Generic.CodeAnalysis.UselessOverridingMethod
     */
    public function __construct(\Magento\Framework\App\ResourceConnection $resource, $modulePrefix = 'importexport')
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
        $maxPacketData = $this->getConnection()->fetchRow('SHOW VARIABLES LIKE "max_allowed_packet"');
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
        $connection = $this->getConnection();
        $sql = sprintf(
            'SELECT AUTO_INCREMENT FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = %s AND TABLE_SCHEMA = DATABASE()',
            $connection->quote($tableName)
        );
        $entityStatus = $connection->fetchRow($sql);
        if (empty($entityStatus['AUTO_INCREMENT'])) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Cannot get autoincrement value'));
        }

        return $entityStatus['AUTO_INCREMENT'];
    }
}

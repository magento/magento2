<?php
/**
 * Backup object factory.
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Backup;

class Factory
{
    /**
     * Object manager
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $_objectManager;

    /**
     * Backup type constant for database backup
     */
    const TYPE_DB = 'db';

    /**
     * Backup type constant for filesystem backup
     */
    const TYPE_FILESYSTEM = 'filesystem';

    /**
     * Backup type constant for full system backup(database + filesystem)
     */
    const TYPE_SYSTEM_SNAPSHOT = 'snapshot';

    /**
     * Backup type constant for media and database backup
     */
    const TYPE_MEDIA = 'media';

    /**
     * Backup type constant for full system backup excluding media folder
     */
    const TYPE_SNAPSHOT_WITHOUT_MEDIA = 'nomedia';

    /**
     * List of supported a backup types
     *
     * @var string[]
     */
    protected $_allowedTypes;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->_objectManager = $objectManager;
        $this->_allowedTypes = [
            self::TYPE_DB,
            self::TYPE_FILESYSTEM,
            self::TYPE_SYSTEM_SNAPSHOT,
            self::TYPE_MEDIA,
            self::TYPE_SNAPSHOT_WITHOUT_MEDIA,
        ];
    }

    /**
     * Create new backup instance
     *
     * @param string $type
     * @return BackupInterface
     * @throws \Magento\Framework\Exception
     */
    public function create($type)
    {
        if (!in_array($type, $this->_allowedTypes)) {
            throw new \Magento\Framework\Exception('Current implementation not supported this type (' . $type . ') of backup.');
        }
        $class = 'Magento\Framework\Backup\\' . ucfirst($type);
        return $this->_objectManager->create($class);
    }
}

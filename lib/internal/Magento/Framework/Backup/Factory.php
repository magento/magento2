<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Backup object factory.
 */
namespace Magento\Framework\Backup;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Phrase;

/**
 * @api
 * @since 100.0.2
 */
class Factory
{
    /**
     * Object manager
     *
     * @var ObjectManagerInterface
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
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
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
     * @throws LocalizedException
     */
    public function create($type)
    {
        if (!in_array($type, $this->_allowedTypes)) {
            throw new LocalizedException(
                new Phrase(
                    'Current implementation not supported this type (%1) of backup.',
                    [$type]
                )
            );
        }
        $class = 'Magento\Framework\Backup\\' . ucfirst($type);
        return $this->_objectManager->create($class);
    }
}

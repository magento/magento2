<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Backup model factory
 *
 * @method \Magento\Backup\Model\Backup create($timestamp, $type)
 */
namespace Magento\Backup\Model;

/**
 * @api
 * @since 2.0.0
 */
class BackupFactory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     * @since 2.0.0
     */
    protected $_objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @since 2.0.0
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * Load backup by it's type and creation timestamp
     *
     * @param int $timestamp
     * @param string $type
     * @return \Magento\Backup\Model\Backup
     * @since 2.0.0
     */
    public function create($timestamp, $type)
    {
        $backupId = $timestamp . '_' . $type;
        $fsCollection = $this->_objectManager->get(\Magento\Backup\Model\Fs\Collection::class);
        $backupInstance = $this->_objectManager->get(\Magento\Backup\Model\Backup::class);
        foreach ($fsCollection as $backup) {
            if ($backup->getId() == $backupId) {
                $backupInstance->setType(
                    $backup->getType()
                )->setTime(
                    $backup->getTime()
                )->setName(
                    $backup->getName()
                )->setPath(
                    $backup->getPath()
                );
                break;
            }
        }
        return $backupInstance;
    }
}

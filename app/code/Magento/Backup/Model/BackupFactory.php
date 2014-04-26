<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Backup model factory
 *
 * @method \Magento\Backup\Model\Backup create($timestamp, $type)
 */
namespace Magento\Backup\Model;

class BackupFactory
{
    /**
     * @var \Magento\Framework\ObjectManager
     */
    protected $_objectManager;

    /**
     * @param \Magento\Framework\ObjectManager $objectManager
     */
    public function __construct(\Magento\Framework\ObjectManager $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * Load backup by it's type and creation timestamp
     *
     * @param int $timestamp
     * @param string $type
     * @return \Magento\Backup\Model\Backup
     */
    public function create($timestamp, $type)
    {
        $backupId = $timestamp . '_' . $type;
        $fsCollection = $this->_objectManager->get('Magento\Backup\Model\Fs\Collection');
        $backupInstance = $this->_objectManager->get('Magento\Backup\Model\Backup');
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

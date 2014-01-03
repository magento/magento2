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
 * @category    Magento
 * @package     Magento_Backup
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Class to work with backups
 *
 * @category    Magento
 * @package     Magento_Backup
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento;

class Backup
{
    /**
     * List of supported a backup types
     *
     * @var array
     */
    static protected $_allowedBackupTypes = array('db', 'snapshot', 'filesystem', 'media', 'nomedia');

    /**
     * get Backup Instance By File Name
     *
     * @param  string $type
     * @return \Magento\Backup\BackupInterface
     */
    static public function getBackupInstance($type)
    {
        $class = 'Magento\Backup\\' . ucfirst($type);

        if (!in_array($type, self::$_allowedBackupTypes) || !class_exists($class, true)){
            throw new \Magento\Exception('Current implementation not supported this type (' . $type . ') of backup.');
        }

        return new $class();
    }
}

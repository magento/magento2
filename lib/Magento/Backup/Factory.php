<?php
/**
 * Backup object factory.
 *
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
 * @copyright Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Backup;

class Factory
{
    /**
     * Object manager
     *
     * @var \Magento\ObjectManager
     */
    private $_objectManager;

    /**
     * List of supported a backup types
     *
     * @var array
     */
    private $_allowedTypes = array('db', 'snapshot', 'filesystem', 'media', 'nomedia');

    /**
     * @param \Magento\ObjectManager $objectManager
     */
    public function __construct(\Magento\ObjectManager $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * Create new backup instance
     *
     * @param string $type
     * @return \Magento\Backup\BackupInterface
     * @throws \Magento\Exception
     */
    public function create($type)
    {
        if (!in_array($type, $this->_allowedTypes)) {
            throw new \Magento\Exception('Current implementation not supported this type (' . $type . ') of backup.');
        }
        $class = 'Magento\Backup\\' . ucfirst($type);
        return $this->_objectManager->create($class);
    }
}

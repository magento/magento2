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
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Generate options for media database selection
 */
class Mage_Adminhtml_Model_System_Config_Source_Storage_Media_Database
{
    /**
     * Store all detected connections
     *
     * @var array
     */
    protected $_connections = array();

    /**
     * Recursively collect connection configuration
     *
     * @param  string $connectionName
     * @return array
     */
    protected function _collectConnectionConfig($connectionName)
    {
        $config = array();

        if (isset($this->_connections[$connectionName])) {
            $connection = $this->_connections[$connectionName];
            $connection = (array) $connection->descend('connection');

            if (isset($connection['use'])) {
                $config = $this->_collectConnectionConfig((string) $connection['use']);
            }

            $config = array_merge($config, $connection);
        }

        return $config;
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $media_storages = array();

        $this->_connections = (array) Mage::app()->getConfig()->getNode('global/resources')->children();
        foreach (array_keys($this->_connections) as $connectionName) {
            $connection = $this->_collectConnectionConfig($connectionName);
            if (!isset($connection['active']) || $connection['active'] != 1) {
                continue;
            }

            $media_storages[] = array('value' => $connectionName, 'label' => $connectionName);
        }
        sort($media_storages);
        reset($media_storages);

        return $media_storages;
    }

}

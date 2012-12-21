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
 * @package     Mage_Core
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Core Resource Resource Model
 *
 * @category    Mage
 * @package     Mage_Core
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Core_Model_Resource_Config extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Define main table
     *
     */
    protected function _construct()
    {
        $this->_init('core_config_data', 'config_id');
    }

    /**
     * Load configuration values into xml config object
     *
     * @param Mage_Core_Model_Config $xmlConfig
     * @param string $condition
     * @return Mage_Core_Model_Resource_Config
     */
    public function loadToXml(Mage_Core_Model_Config $xmlConfig, $condition = null)
    {
        $read = $this->_getReadAdapter();
        if (!$read) {
            return $this;
        }

        $websites = array();
        $select = $read->select()
            ->from($this->getTable('core_website'), array('website_id', 'code', 'name'));
        $rowset = $read->fetchAssoc($select);
        foreach ($rowset as $w) {
            $xmlConfig->setNode('websites/' . $w['code'] . '/system/website/id', $w['website_id']);
            $xmlConfig->setNode('websites/' . $w['code'] . '/system/website/name', $w['name']);
            $websites[$w['website_id']] = array('code' => $w['code']);
        }

        $stores = array();
        $select = $read->select()
            ->from($this->getTable('core_store'), array('store_id', 'code', 'name', 'website_id'))
            ->order('sort_order ' . Varien_Db_Select::SQL_ASC);
        $rowset = $read->fetchAssoc($select);
        foreach ($rowset as $s) {
            if (!isset($websites[$s['website_id']])) {
                continue;
            }
            $xmlConfig->setNode('stores/' . $s['code'] . '/system/store/id', $s['store_id']);
            $xmlConfig->setNode('stores/' . $s['code'] . '/system/store/name', $s['name']);
            $xmlConfig->setNode('stores/' . $s['code'] . '/system/website/id', $s['website_id']);
            $xmlConfig->setNode(
                'websites/' . $websites[$s['website_id']]['code'] . '/system/stores/' . $s['code'],
                $s['store_id']
            );
            $stores[$s['store_id']] = array('code'=>$s['code']);
            $websites[$s['website_id']][Mage_Core_Model_Config::SCOPE_STORES][$s['store_id']] = $s['code'];
        }

        $substFrom = array();
        $substTo   = array();

        // load all configuration records from database, which are not inherited
        $select = $read->select()
            ->from($this->getMainTable(), array('scope', 'scope_id', 'path', 'value'));
        if (!is_null($condition)) {
            $select->where($condition);
        }
        $rowset = $read->fetchAll($select);


        // set default config values from database
        foreach ($rowset as $r) {
            if ($r['scope'] !== Mage_Core_Model_Store::DEFAULT_CODE) {
                continue;
            }
            $value = str_replace($substFrom, $substTo, $r['value']);
            $xmlConfig->setNode('default/' . $r['path'], $value);
        }

        // inherit default config values to all websites
        $extendSource = $xmlConfig->getNode('default');
        foreach ($websites as $id=>$w) {
            $websiteNode = $xmlConfig->getNode('websites/' . $w['code']);
            $websiteNode->extend($extendSource);
        }

        $deleteWebsites = array();
        // set websites config values from database
        foreach ($rowset as $r) {
            if ($r['scope'] !== Mage_Core_Model_Config::SCOPE_WEBSITES) {
                continue;
            }
            $value = str_replace($substFrom, $substTo, $r['value']);
            if (isset($websites[$r['scope_id']])) {
                $nodePath = sprintf('websites/%s/%s', $websites[$r['scope_id']]['code'], $r['path']);
                $xmlConfig->setNode($nodePath, $value);
            } else {
                $deleteWebsites[$r['scope_id']] = $r['scope_id'];
            }
        }

        // extend website config values to all associated stores
        foreach ($websites as $website) {
            $extendSource = $xmlConfig->getNode('websites/' . $website['code']);
            if (isset($website[Mage_Core_Model_Config::SCOPE_STORES])) {
                foreach ($website[Mage_Core_Model_Config::SCOPE_STORES] as $sCode) {
                    $storeNode = $xmlConfig->getNode('stores/' . $sCode);
                    /**
                     * $extendSource DO NOT need overwrite source
                     */
                    $storeNode->extend($extendSource, false);
                }
            }
        }

        $deleteStores = array();
        // set stores config values from database
        foreach ($rowset as $r) {
            if ($r['scope'] !== Mage_Core_Model_Config::SCOPE_STORES) {
                continue;
            }
            $value = str_replace($substFrom, $substTo, $r['value']);
            if (isset($stores[$r['scope_id']])) {
                $nodePath = sprintf('stores/%s/%s', $stores[$r['scope_id']]['code'], $r['path']);
                $xmlConfig->setNode($nodePath, $value);
            } else {
                $deleteStores[$r['scope_id']] = $r['scope_id'];
            }
        }

        if ($deleteWebsites) {
            $this->_getWriteAdapter()->delete($this->getMainTable(), array(
                'scope = ?'      => Mage_Core_Model_Config::SCOPE_WEBSITES,
                'scope_id IN(?)' => $deleteWebsites,
            ));
        }

        if ($deleteStores) {
            $this->_getWriteAdapter()->delete($this->getMainTable(), array(
                'scope=?'        => Mage_Core_Model_Config::SCOPE_STORES,
                'scope_id IN(?)' => $deleteStores,
            ));
        }
        return $this;
    }

    /**
     * Save config value
     *
     * @param string $path
     * @param string $value
     * @param string $scope
     * @param int $scopeId
     * @return Mage_Core_Model_Resource_Config
     */
    public function saveConfig($path, $value, $scope, $scopeId)
    {
        $writeAdapter = $this->_getWriteAdapter();
        $select = $writeAdapter->select()
            ->from($this->getMainTable())
            ->where('path = ?', $path)
            ->where('scope = ?', $scope)
            ->where('scope_id = ?', $scopeId);
        $row = $writeAdapter->fetchRow($select);

        $newData = array(
            'scope'     => $scope,
            'scope_id'  => $scopeId,
            'path'      => $path,
            'value'     => $value
        );

        if ($row) {
            $whereCondition = array($this->getIdFieldName() . '=?' => $row[$this->getIdFieldName()]);
            $writeAdapter->update($this->getMainTable(), $newData, $whereCondition);
        } else {
            $writeAdapter->insert($this->getMainTable(), $newData);
        }
        return $this;
    }

    /**
     * Delete config value
     *
     * @param string $path
     * @param string $scope
     * @param int $scopeId
     * @return Mage_Core_Model_Resource_Config
     */
    public function deleteConfig($path, $scope, $scopeId)
    {
        $adapter = $this->_getWriteAdapter();
        $adapter->delete($this->getMainTable(), array(
            $adapter->quoteInto('path = ?', $path),
            $adapter->quoteInto('scope = ?', $scope),
            $adapter->quoteInto('scope_id = ?', $scopeId)
        ));
        return $this;
    }
}

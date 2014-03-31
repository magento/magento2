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
 * @package     Magento_Core
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Core\Model\Resource\Config;

use Magento\Core\Model\Website;

/**
 * Core config data resource model
 *
 * @category    Magento
 * @package     Magento_Core
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Data extends \Magento\Model\Resource\Db\AbstractDb
{
    /**
     * Define main table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('core_config_data', 'config_id');
    }

    /**
     * Convert array to comma separated value
     *
     * @param \Magento\Model\AbstractModel $object
     * @return $this
     */
    protected function _beforeSave(\Magento\Model\AbstractModel $object)
    {
        if (!$object->getId()) {
            $this->_checkUnique($object);
        }

        if (is_array($object->getValue())) {
            $object->setValue(join(',', $object->getValue()));
        }
        return parent::_beforeSave($object);
    }

    /**
     * Validate unique configuration data before save
     * Set id to object if exists configuration instead of throw exception
     *
     * @param \Magento\Model\AbstractModel $object
     * @return $this
     */
    protected function _checkUnique(\Magento\Model\AbstractModel $object)
    {
        $select = $this->_getReadAdapter()->select()->from(
            $this->getMainTable(),
            array($this->getIdFieldName())
        )->where(
            'scope = :scope'
        )->where(
            'scope_id = :scope_id'
        )->where(
            'path = :path'
        );
        $bind = array(
            'scope' => $object->getScope(),
            'scope_id' => $object->getScopeId(),
            'path' => $object->getPath()
        );

        $configId = $this->_getReadAdapter()->fetchOne($select, $bind);
        if ($configId) {
            $object->setId($configId);
        }

        return $this;
    }

    /**
     * Clear website data
     *
     * @param Website $website
     * @return void
     */
    public function clearWebsiteData(Website $website)
    {
        $this->_getWriteAdapter()->delete(
            $this->getMainTable(),
            array('scope = ?' => 'websites', 'scope_id' => $website->getId())
        );
        $this->clearStoreData($website->getStoreIds());
    }

    /**
     * Clear store data
     *
     * @param array $storeIds
     * @return void
     */
    public function clearStoreData(array $storeIds)
    {
        $this->_getWriteAdapter()->delete(
            $this->getMainTable(),
            array('scope = ?' => 'stores', 'scope_id IN (?)' => $storeIds)
        );
    }
}

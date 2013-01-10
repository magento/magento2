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
 * @package     Mage_Paypal
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * PayPal resource model for certificate based authentication
 *
 * @category    Mage
 * @package     Mage_Paypal
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Paypal_Model_Resource_Cert extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Initialize connection
     */
    protected function _construct()
    {
        $this->_init('paypal_cert', 'cert_id');
    }

    /**
     * Set date of last update
     *
     * @param Mage_Core_Model_Abstract $object
     * @return Mage_Core_Model_Resource_Db_Abstract
     */
    protected function _beforeSave(Mage_Core_Model_Abstract $object)
    {
        $object->setUpdatedAt($this->formatDate(Mage::getSingleton('Mage_Core_Model_Date')->gmtDate()));
        return parent::_beforeSave($object);
    }

    /**
     * Load model by website id
     *
     * @param Mage_Paypal_Model_Cert $object
     * @param bool $strictLoad
     * @return Mage_Paypal_Model_Cert
     */
    public function loadByWebsite($object, $strictLoad = true)
    {
        $adapter = $this->_getReadAdapter();
        $select  = $adapter->select()->from(array('main_table' => $this->getMainTable()));

        if ($strictLoad) {
            $select->where('main_table.website_id =?', $object->getWebsiteId());
        } else {
            $select->where('main_table.website_id IN(0, ?)', $object->getWebsiteId())
                ->order('main_table.website_id DESC')
                ->limit(1);
        }

        $data = $adapter->fetchRow($select);
        if ($data) {
            $object->setData($data);
        }
        return $object;
    }
}

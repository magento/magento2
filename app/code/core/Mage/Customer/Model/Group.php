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
 * @package     Mage_Customer
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Customer group model
 *
 * @method Mage_Customer_Model_Resource_Group _getResource()
 * @method Mage_Customer_Model_Resource_Group getResource()
 * @method string getCustomerGroupCode()
 * @method Mage_Customer_Model_Group setCustomerGroupCode(string $value)
 * @method Mage_Customer_Model_Group setTaxClassId(int $value)
 *
 * @category    Mage
 * @package     Mage_Customer
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Customer_Model_Group extends Mage_Core_Model_Abstract
{
    /**
     * Xml config path for create account default group
     */
    const XML_PATH_DEFAULT_ID       = 'customer/create_account/default_group';

    const NOT_LOGGED_IN_ID          = 0;
    const CUST_GROUP_ALL            = 32000;

    const ENTITY                    = 'customer_group';

    const GROUP_CODE_MAX_LENGTH     = 32;

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'customer_group';

    /**
     * Parameter name in event
     *
     * In observe method you can use $observer->getEvent()->getObject() in this case
     *
     * @var string
     */
    protected $_eventObject = 'object';

    protected static $_taxClassIds = array();

    protected function _construct()
    {
        $this->_init('Mage_Customer_Model_Resource_Group');
    }

    /**
     * Alias for setCustomerGroupCode
     *
     * @param string $value
     */
    public function setCode($value)
    {
        return $this->setCustomerGroupCode($value);
    }

    /**
     * Alias for getCustomerGroupCode
     *
     * @return string
     */
    public function getCode()
    {
        return $this->getCustomerGroupCode();
    }

    public function getTaxClassId($groupId = null)
    {
        if (!is_null($groupId)) {
            if (empty(self::$_taxClassIds[$groupId])) {
                $this->load($groupId);
                self::$_taxClassIds[$groupId] = $this->getData('tax_class_id');
            }
            $this->setData('tax_class_id', self::$_taxClassIds[$groupId]);
        }
        return $this->getData('tax_class_id');
    }


    public function usesAsDefault()
    {
        $data = Mage::getConfig()->getStoresConfigByPath(self::XML_PATH_DEFAULT_ID);
        if (in_array($this->getId(), $data)) {
            return true;
        }
        return false;
    }

    /**
     * Run reindex process after data save
     *
     * @return Mage_Customer_Model_Group
     */
    protected function _afterSave()
    {
        parent::_afterSave();
        Mage::getSingleton('Mage_Index_Model_Indexer')->processEntityAction(
            $this, self::ENTITY, Mage_Index_Model_Event::TYPE_SAVE
        );
        return $this;
    }

    /**
     * Prepare data before save
     *
     * @return Mage_Core_Model_Abstract
     */
    protected function _beforeSave()
    {
        $this->_prepareData();
        return parent::_beforeSave();
    }

    /**
     * Prepare customer group data
     *
     * @return Mage_Customer_Model_Group
     */
    protected function _prepareData()
    {
        $this->setCode(
            substr($this->getCode(), 0, self::GROUP_CODE_MAX_LENGTH)
        );
        return $this;
    }

}

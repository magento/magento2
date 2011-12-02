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
 * @package     Mage_Eav
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Eav Form Fieldset Resource Collection
 *
 * @category    Mage
 * @package     Mage_Eav
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Eav_Model_Resource_Form_Fieldset_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /**
     * Store scope ID
     *
     * @var int
     */
    protected $_storeId;

    /**
     * Initialize collection model
     *
     */
    protected function _construct()
    {
        $this->_init('Mage_Eav_Model_Form_Fieldset', 'Mage_Eav_Model_Resource_Form_Fieldset');
    }

    /**
     * Add Form Type filter to collection
     *
     * @param Mage_Eav_Model_Form_Type|int $type
     * @return Mage_Eav_Model_Resource_Form_Fieldset_Collection
     */
    public function addTypeFilter($type)
    {
        if ($type instanceof Mage_Eav_Model_Form_Type) {
            $type = $type->getId();
        }

        return $this->addFieldToFilter('type_id', $type);
    }

    /**
     * Set order by fieldset sort order
     *
     * @return Mage_Eav_Model_Resource_Form_Fieldset_Collection
     */
    public function setSortOrder()
    {
        $this->setOrder('sort_order', self::SORT_ORDER_ASC);
        return $this;
    }

    /**
     * Retrieve label store scope
     *
     * @return int
     */
    public function getStoreId()
    {
        if (is_null($this->_storeId)) {
            return Mage::app()->getStore()->getId();
        }
        return $this->_storeId;
    }

    /**
     * Set store scope ID
     *
     * @param int $storeId
     * @return Mage_Eav_Model_Resource_Form_Fieldset_Collection
     */
    public function setStoreId($storeId)
    {
        $this->_storeId = $storeId;
        return $this;
    }

    /**
     * Initialize select object
     *
     * @return Mage_Eav_Model_Resource_Form_Fieldset_Collection
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $select = $this->getSelect();
        $select->join(
            array('default_label' => $this->getTable('eav_form_fieldset_label')),
            'main_table.fieldset_id = default_label.fieldset_id AND default_label.store_id = 0',
            array());
        if ($this->getStoreId() == 0) {
            $select->columns('label', 'default_label');
        } else {
            $labelExpr = $select->getAdapter()
                ->getIfNullSql('store_label.label', 'default_label.label');
            $joinCondition = $this->getConnection()
                ->quoteInto(
                    'main_table.fieldset_id = store_label.fieldset_id AND store_label.store_id = ?', 
                    (int)$this->getStoreId());
            $select->joinLeft(
                array('store_label' => $this->getTable('eav_form_fieldset_label')),
                $joinCondition,
                array('label' => $labelExpr)
            );
        }

        return $this;
    }
}

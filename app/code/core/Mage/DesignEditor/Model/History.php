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
 * @package     Mage_DesignEditor
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Visual Design Editor history model
 */
class Mage_DesignEditor_Model_History extends Mage_Backend_Model_Auth_Session
{
    /**
     * Required change fields
     *
     * @var array
     */
    protected $_requiredFields = array('handle', 'change_type', 'element_name', 'action_name');


    /**
     * Change log
     *
     * @var array
     */
    protected $_changeLog = array();

    /**
     * Manager model
     *
     * @var null|Mage_DesignEditor_Model_History_Manager
     */
    protected $_managerModel;

    /**
     * Get compact log
     *
     * @return array
     */
    public function getCompactLog()
    {
        return $this->_compactLog()->_getManagerModel()->getHistoryLog();
    }

    /**
     * Get compact xml
     *
     * @return string
     */
    public function getCompactXml()
    {
        return $this->_compactLog()->_getManagerModel()->getXml();
    }

    /**
     * Set change log
     *
     * @param array $changeLog
     * @return Mage_DesignEditor_Model_History
     */
    public function setChangeLog($changeLog)
    {
        $this->_changeLog = $changeLog;
        return $this;
    }

    /**
     * Compact log
     *
     * @return Mage_DesignEditor_Model_History
     */
    protected function _compactLog()
    {
        $managerModel = $this->_getManagerModel();
        foreach ($this->_getChangeLog() as $change) {
            $this->_validateChange($change);
            $managerModel->addChange($change);
        }

        return $this;
    }

    /**
     * Get change log
     *
     * @return array
     */
    protected function _getChangeLog()
    {
        return $this->_changeLog;
    }

    /**
     * Get change model
     *
     * @return Mage_DesignEditor_Model_History_Manager
     */
    protected function _getManagerModel()
    {
        if ($this->_managerModel == null) {
            $this->_managerModel = Mage::getModel('Mage_DesignEditor_Model_History_Manager');
        }
        return $this->_managerModel;
    }

    /**
     * Validate change
     *
     * @throws Mage_DesignEditor_Exception
     * @param array $change
     * @return Mage_DesignEditor_Model_History
     */
    protected function _validateChange($change)
    {
        foreach ($this->_requiredFields as $field) {
            if (!is_array($change) || !array_key_exists($field, $change) || empty($change[$field])) {
                throw new Mage_DesignEditor_Exception(
                    Mage::helper('Mage_DesignEditor_Helper_Data')->__('Invalid change data')
                );
            }
        }
        return $this;
    }
}

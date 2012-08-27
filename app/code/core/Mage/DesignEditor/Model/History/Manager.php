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
 * Visual design editor manager model
 */
class Mage_DesignEditor_Model_History_Manager extends Mage_Core_Model_Abstract
{
    /**
     * Change collection
     *
     * @var null|Mage_DesignEditor_Model_History_Manager_Collection
     */
    protected $_changeCollection;

    /**
     * Add change
     *
     * @param array $change
     * @return Mage_DesignEditor_Model_History_Manager
     */
    public function addChange($change)
    {
        $this->_getChangeCollection()->addElement($change);
        return $this;
    }

    /**
     * Get history log
     *
     * @return array
     */
    public function getHistoryLog()
    {
        return $this->_getChangeCollection()->toHistoryLog();
    }

    /**
     * Get xml changes
     *
     * @return string
     */
    public function getXml()
    {
        return $this->_getChangeCollection()->toXml();
    }

    /**
     * Get change collection
     *
     * @return Mage_DesignEditor_Model_History_Manager_Collection
     */
    protected function _getChangeCollection()
    {
        if ($this->_changeCollection == null) {
            $this->_changeCollection = Mage::getModel('Mage_DesignEditor_Model_History_Manager_Collection');
        }
        return $this->_changeCollection;
    }
}

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
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * History compaction model
 */
class Mage_DesignEditor_Model_History_Compact
{
    /**
     * Configuration for compact model
     *
     * @var array
     */
    protected $_config = array('Mage_DesignEditor_Model_History_Compact_Layout');

    /**
     * Storage of compact strategies
     *
     * @var array
     */
    protected $_compactModels = array();

    /**
     * Compact collection of changes
     *
     * @param Mage_DesignEditor_Model_Change_Collection $collection
     * @throws Mage_Core_Exception
     * @return Mage_DesignEditor_Model_History_Compact
     */
    public function compact(Mage_DesignEditor_Model_Change_Collection $collection)
    {
        $itemType = $collection->getItemClass();
        if (!$itemType == 'Mage_DesignEditor_Model_ChangeAbstract') {
            Mage::throwException(
                Mage::helper('Mage_DesignEditor_Helper_Data')->__('Invalid collection items\' type "%s"', $itemType)
            );
        }

        /** @var $model Mage_DesignEditor_Model_History_CompactInterface */
        foreach ($this->_getCompactModels() as $model) {
            $model->compact($collection);
        }

        return $this;
    }

    /**
     * Get compaction strategies array ordered to minimize performance impact
     *
     * @return array
     */
    protected function _getCompactModels()
    {
        if (!$this->_compactModels) {
            foreach ($this->_getConfig() as $class) {
                $this->_compactModels[] = Mage::getModel($class);
            }
        }

        return $this->_compactModels;
    }

    /**
     * Get configuration for compact
     *
     * @return array
     */
    protected function _getConfig()
    {
        return $this->_config;
    }
}

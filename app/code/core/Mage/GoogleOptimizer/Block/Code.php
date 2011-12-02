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
 * @package     Mage_GoogleOptimizer
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Google Optimizer Scripts Block
 *
 * @category   Mage
 * @package    Mage_GoogleOptimizer
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_GoogleOptimizer_Block_Code extends Mage_Core_Block_Template
{
    protected $_scriptType          = null;
    protected $_googleOptmizerModel = null;
    protected $_avaibleScriptTypes = array('control_script', 'tracking_script', 'conversion_script');

    /**
     * override this method if need something special for type of script
     *
     * @return Mage_GoogleOptimizer_Block_Code
     */
    protected function _initGoogleOptimizerModel()
    {
        return $this;
    }

    /**
     * Setting google optimizer model
     *
     * @param Varien_Object $model
     * @return Mage_GoogleOptimizer_Block_Code
     */
    protected function _setGoogleOptimizerModel($model)
    {
        $this->_googleOptmizerModel = $model;
        return $this;
    }

    /**
     * Return google optimizer model
     *
     * @return Varien_Object
     */
    protected function _getGoogleOptimizerModel()
    {
        return $this->_googleOptmizerModel;
    }

    protected function _toHtml()
    {
        return parent::_toHtml() . $this->getScriptCode();
    }

    /**
     * Return script by type $this->_scriptType
     *
     * @return string
     */
    public function getScriptCode()
    {
        if (!Mage::helper('Mage_GoogleOptimizer_Helper_Data')->isOptimizerActive()) {
            return '';
        }
        if (is_null($this->_scriptType)) {
            return '';
        }
        $this->_initGoogleOptimizerModel();
        if (!($this->_getGoogleOptimizerModel() instanceof Varien_Object)) {
            return '';
        }
        return $this->_getGoogleOptimizerModel()->getData($this->_scriptType);
    }

    /**
     * Check than set script type
     *
     * @param string $scriptType
     * @return Mage_GoogleOptimizer_Block_Code
     */
    public function setScriptType($scriptType)
    {
        if (in_array($scriptType, $this->_avaibleScriptTypes)) {
            $this->_scriptType = $scriptType;
        }
        return $this;
    }
}

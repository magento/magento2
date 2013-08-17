<?php
/**
 * Abstract Google Experiment Tab
 *
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
 * @copyright Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class Mage_GoogleOptimizer_Block_Adminhtml_TabAbstract
    extends Mage_Backend_Block_Widget_Form implements Mage_Backend_Block_Widget_Tab_Interface
{
    /**
     * @var Mage_GoogleOptimizer_Helper_Data
     */
    protected $_helperData;

    /**
     * @var Mage_Core_Model_Registry
     */
    protected $_registry;

    /**
     * @var Mage_GoogleOptimizer_Helper_Code
     */
    protected $_codeHelper;

    /**
     * @var Mage_GoogleOptimizer_Helper_Form
     */
    protected $_formHelper;

    /**
     * @param Mage_Backend_Block_Template_Context $context
     * @param Mage_GoogleOptimizer_Helper_Data $helperData
     * @param Mage_Core_Model_Registry $registry
     * @param Mage_GoogleOptimizer_Helper_Code $codeHelper
     * @param Mage_GoogleOptimizer_Helper_Form $formHelper
     * @param Varien_Data_Form $form
     * @param array $data
     */
    public function __construct(
        Mage_Backend_Block_Template_Context $context,
        Mage_GoogleOptimizer_Helper_Data $helperData,
        Mage_Core_Model_Registry $registry,
        Mage_GoogleOptimizer_Helper_Code $codeHelper,
        Mage_GoogleOptimizer_Helper_Form $formHelper,
        Varien_Data_Form $form,
        array $data = array()
    ) {
        parent::__construct($context, $data);

        $this->_helperData = $helperData;
        $this->_registry = $registry;
        $this->_codeHelper = $codeHelper;
        $this->_formHelper = $formHelper;
        $this->setForm($form);
    }

    /**
     * Prepare form before rendering HTML
     *
     * @return Mage_Backend_Block_Widget_Form
     */
    protected function _prepareForm()
    {
        $this->_formHelper->addGoogleoptimizerFields($this->getForm(), $this->_getGoogleExperiment());
        return parent::_prepareForm();
    }

    /**
     * Get google experiment code model
     *
     * @return Mage_GoogleOptimizer_Model_Code|null
     */
    protected function _getGoogleExperiment()
    {
        $entity = $this->_getEntity();
        if ($entity->getId()) {
            return $this->_codeHelper->getCodeObjectByEntity($entity);
        }
        return null;
    }

    /**
     * Get Entity model
     *
     * @return Mage_Catalog_Model_Abstract
     */
    protected abstract function _getEntity();

    /**
     * Can show tab in tabs
     *
     * @return boolean
     */
    public function canShowTab()
    {
        return $this->_helperData->isGoogleExperimentActive();
    }

    /**
     * Tab is hidden
     *
     * @return boolean
     */
    public function isHidden()
    {
        return false;
    }
}

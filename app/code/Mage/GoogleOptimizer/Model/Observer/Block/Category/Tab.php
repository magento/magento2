<?php
/**
 * Google Optimizer Observer Category Tab
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
class Mage_GoogleOptimizer_Model_Observer_Block_Category_Tab
{
    /**
     * @var Mage_GoogleOptimizer_Helper_Data
     */
    protected $_helper;

    /**
     * @var Mage_Core_Model_Layout
     */
    protected $_layout;

    /**
     * @param Mage_GoogleOptimizer_Helper_Data $helper
     * @param Mage_Core_Model_Layout $layout
     */
    public function __construct(Mage_GoogleOptimizer_Helper_Data $helper, Mage_Core_Model_Layout $layout)
    {
        $this->_helper = $helper;
        $this->_layout = $layout;
    }

    /**
     * Adds Google Experiment tab to the category edit page
     *
     * @param Varien_Event_Observer $observer
     */
    public function addGoogleExperimentTab(Varien_Event_Observer $observer)
    {
        if ($this->_helper->isGoogleExperimentActive()) {
            $block = $this->_layout->createBlock(
                'Mage_GoogleOptimizer_Block_Adminhtml_Catalog_Category_Edit_Tab_Googleoptimizer',
                'google-experiment-form'
            );

            /** @var $tabs Mage_Adminhtml_Block_Catalog_Category_Tabs */
            $tabs = $observer->getEvent()->getTabs();
            $tabs->addTab('google-experiment-tab', array(
                'label' => $this->_helper->__('Category View Optimization'),
                'content' => $block->toHtml(),
            ));
        }
    }
}

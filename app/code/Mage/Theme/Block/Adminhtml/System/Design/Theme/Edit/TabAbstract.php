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
 * @package     Mage_Theme
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Theme form tab abstract block
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
abstract class Mage_Theme_Block_Adminhtml_System_Design_Theme_Edit_TabAbstract
    extends Mage_Backend_Block_Widget_Form
    implements Mage_Backend_Block_Widget_Tab_Interface
{
    /**
     * @var Magento_ObjectManager
     */
    protected $_objectManager;

    /**
     * @param Mage_Backend_Block_Template_Context $context
     * @param Magento_ObjectManager $objectManager
     * @param array $data
     */
    public function __construct(
        Mage_Backend_Block_Template_Context $context,
        Magento_ObjectManager $objectManager,
        array $data = array()
    ) {
        parent::__construct($context, $data);
        $this->_objectManager = $objectManager;
    }

    /**
     * Get theme that is edited currently
     *
     * @return Mage_Core_Model_Theme
     */
    protected function _getCurrentTheme()
    {
        return Mage::registry('current_theme');
    }

    /**
     * Return Tab title
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->getTabLabel();
    }

    /**
     * Can show tab in tabs
     *
     * @return boolean
     */
    public function canShowTab()
    {
        return $this->_getCurrentTheme()->isVirtual() && $this->_getCurrentTheme()->getId();
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

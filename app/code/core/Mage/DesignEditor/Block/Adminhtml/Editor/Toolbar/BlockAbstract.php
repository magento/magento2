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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Abstract toolbar block
 */
abstract class Mage_DesignEditor_Block_Adminhtml_Editor_Toolbar_BlockAbstract extends Mage_Backend_Block_Template
{
    /**
     * Current VDE mode
     *
     * @var int
     */
    protected $_mode;

    /**
     * Get current VDE mode
     *
     * @return int
     */
    public function getMode()
    {
        return $this->_mode;
    }

    /**
     * Get current VDE mode
     *
     * @param int $mode
     * @return Mage_DesignEditor_Block_Adminhtml_Editor_Toolbar_BlockAbstract
     */
    public function setMode($mode)
    {
        $this->_mode = $mode;

        return $this;
    }

    /**
     * Check if visual editor is in design mode
     *
     * @return bool
     */
    public function isDesignMode()
    {
        return $this->getMode() == Mage_DesignEditor_Model_State::MODE_DESIGN;
    }

    /**
     * Check if visual editor is in navigation mode
     *
     * @return bool
     */
    public function isNavigationMode()
    {
        return $this->getMode() == Mage_DesignEditor_Model_State::MODE_NAVIGATION;
    }
}

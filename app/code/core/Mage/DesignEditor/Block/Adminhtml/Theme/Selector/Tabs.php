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
 * Theme selectors tabs container
 */
class Mage_DesignEditor_Block_Adminhtml_Theme_Selector_Tabs extends Mage_Backend_Block_Widget_Tabs
{
    /**
     * Initialize tab
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('theme_selector_tabs');
        $this->setDestElementId('theme_selector');
        $this->setTitle($this->__('Design & Theme Gallery'));
        $this->setIsHoriz(true);
    }

    /**
     * Add content container to template
     *
     * @return string
     */
    protected function _toHtml()
    {
        return parent::_toHtml() . '<div id="' . $this->getDestElementId() . '"><div>';
    }
}

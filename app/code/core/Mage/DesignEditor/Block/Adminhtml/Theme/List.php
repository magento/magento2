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
 * Design editor theme list
 */
class Mage_DesignEditor_Block_Adminhtml_Theme_List extends Mage_Backend_Block_Widget_Container
{
    /**
     * So called "container controller" to specify group of blocks participating in some action
     *
     * @var string
     */
    protected $_controller = 'vde';

    /**
     * Get header text
     *
     * @return string
     */
    public function getHeaderText()
    {
        return $this->__('Themes List');
    }

    /**
     * Get list items of themes
     *
     * @param bool $isFeatured
     * @return array
     */
    public function getListItems($isFeatured = true)
    {
        /** @var $itemBlock Mage_DesignEditor_Block_Adminhtml_Theme_Item */
        $itemBlock = $this->getChildBlock('item');

        /** @var $model Mage_Core_Model_Resource_Theme_Collection */
        $themeCollection = Mage::getResourceModel('Mage_Core_Model_Resource_Theme_Collection');

        $items = array();
        /** @var $theme Mage_Core_Model_Theme */
        foreach ($themeCollection as $theme) {
            if ($isFeatured != $theme->getIsFeatured()) {
                continue;
            }
            $itemBlock->setTheme($theme);
            $items[] = $this->getChildHtml('item', false);
        }

        return $items;
    }
}

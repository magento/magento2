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
 * @package     Mage_Backend
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Backend menu item block
 *
 * @category   Mage
 * @package    Mage_Backend
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Backend_Block_Menu_Item extends Mage_Backend_Block_Template
{
    /**
     * @var Mage_Backend_Block_Menu
     */
    protected $_containerRenderer;

    /**
     * @var Mage_Backend_Model_Menu_Item
     */
    protected $_menuItem;

    /**
     * Set menu model
     * @return Mage_Backend_Model_Menu_Item
     */
    public function getMenuItem()
    {
        return $this->_menuItem;
    }

    /**
     * Get menu item model
     *
     * @param Mage_Backend_Model_Menu_Item $menuItem
     * @return Mage_Backend_Block_Menu_Item
     */
    public function setMenuItem(Mage_Backend_Model_Menu_Item $menuItem)
    {
        $this->_menuItem = $menuItem;
        return $this;
    }

    /**
     * Check whether given item is currently selected
     *
     * @param Mage_Backend_Model_Menu_Item $item
     * @return bool
     */
    public function isItemActive(Mage_Backend_Model_Menu_Item $item)
    {
        $itemModel = $this->getContainerRenderer()->getActiveItemModel();
        $output = false;

        if ($this->getLevel() == 0
            && $itemModel instanceof Mage_Backend_Model_Menu_Item
            && ($itemModel->getId() == $item->getId()
                || (strpos($itemModel->getFullPath(), $item->getFullPath() . '/') === 0))
        ) {
            $output = true;
        }
        return $output;
    }

    /**
     * Current menu item is last
     * @return bool
     */
    public function isLast()
    {
        return ($this->getLevel() == 0
            && (bool)$this->getContainerRenderer()->getMenuModel()->isLast($this->getMenuItem()));
    }

    /**
     * @return Mage_Backend_Block_Menu
     */
    public function getContainerRenderer()
    {
        return $this->_containerRenderer;
    }

    /**
     * @param Mage_Backend_Block_Menu $block
     * @return Mage_Backend_Block_Menu_Item
     */
    public function setContainerRenderer(Mage_Backend_Block_Menu $block)
    {
        $this->_containerRenderer = $block;
        return $this;
    }
}

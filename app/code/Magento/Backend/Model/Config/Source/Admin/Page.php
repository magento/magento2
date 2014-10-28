<?php
/**
 * Admin system config startup page
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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Backend\Model\Config\Source\Admin;

class Page implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Menu model
     *
     * @var \Magento\Backend\Model\Menu
     */
    protected $_menu;

    /**
     * @var \Magento\Backend\Model\Menu\Filter\IteratorFactory
     */
    protected $_iteratorFactory;

    /**
     * @param \Magento\Backend\Model\Menu\Filter\IteratorFactory $iteratorFactory
     * @param \Magento\Backend\Model\Menu\Config $menuConfig
     */
    public function __construct(
        \Magento\Backend\Model\Menu\Filter\IteratorFactory $iteratorFactory,
        \Magento\Backend\Model\Menu\Config $menuConfig
    ) {
        $this->_menu = $menuConfig->getMenu();
        $this->_iteratorFactory = $iteratorFactory;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = array();
        $this->_createOptions($options, $this->_menu);
        return $options;
    }

    /**
     * Get menu filter iterator
     *
     * @param \Magento\Backend\Model\Menu $menu menu model
     * @return \Magento\Backend\Model\Menu\Filter\Iterator
     */
    protected function _getMenuIterator(\Magento\Backend\Model\Menu $menu)
    {
        return $this->_iteratorFactory->create(array('iterator' => $menu->getIterator()));
    }

    /**
     * Create options array
     *
     * @param array &$optionArray
     * @param \Magento\Backend\Model\Menu $menu
     * @param int $level
     * @return void
     */
    protected function _createOptions(&$optionArray, \Magento\Backend\Model\Menu $menu, $level = 0)
    {
        $nonEscapableNbspChar = html_entity_decode('&#160;', ENT_NOQUOTES, 'UTF-8');
        $paddingString = str_repeat($nonEscapableNbspChar, $level * 4);

        foreach ($this->_getMenuIterator($menu) as $menuItem) {

            /**@var  $menuItem \Magento\Backend\Model\Menu\Item */
            if ($menuItem->getAction()) {
                $optionArray[] = array(
                    'label' => $paddingString . $menuItem->getTitle(),
                    'value' => $menuItem->getId()
                );

                if ($menuItem->hasChildren()) {
                    $this->_createOptions($optionArray, $menuItem->getChildren(), $level + 1);
                }
            } else {
                $children = array();

                if ($menuItem->hasChildren()) {
                    $this->_createOptions($children, $menuItem->getChildren(), $level + 1);
                }

                $optionArray[] = array('label' => $paddingString . $menuItem->getTitle(), 'value' => $children);
            }
        }
    }
}

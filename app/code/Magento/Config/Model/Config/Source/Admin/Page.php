<?php
/**
 * Admin system config startup page
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Model\Config\Source\Admin;

/**
 * @api
 * @since 2.0.0
 */
class Page implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Menu model
     *
     * @var \Magento\Backend\Model\Menu
     * @since 2.0.0
     */
    protected $_menu;

    /**
     * @var \Magento\Backend\Model\Menu\Filter\IteratorFactory
     * @since 2.0.0
     */
    protected $_iteratorFactory;

    /**
     * @param \Magento\Backend\Model\Menu\Filter\IteratorFactory $iteratorFactory
     * @param \Magento\Backend\Model\Menu\Config $menuConfig
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function toOptionArray()
    {
        $options = [];
        $this->_createOptions($options, $this->_menu);
        return $options;
    }

    /**
     * Get menu filter iterator
     *
     * @param \Magento\Backend\Model\Menu $menu menu model
     * @return \Magento\Backend\Model\Menu\Filter\Iterator
     * @since 2.0.0
     */
    protected function _getMenuIterator(\Magento\Backend\Model\Menu $menu)
    {
        return $this->_iteratorFactory->create(['iterator' => $menu->getIterator()]);
    }

    /**
     * Create options array
     *
     * @param array &$optionArray
     * @param \Magento\Backend\Model\Menu $menu
     * @param int $level
     * @return void
     * @since 2.0.0
     */
    protected function _createOptions(&$optionArray, \Magento\Backend\Model\Menu $menu, $level = 0)
    {
        $nonEscapableNbspChar = html_entity_decode('&#160;', ENT_NOQUOTES, 'UTF-8');
        $paddingString = str_repeat($nonEscapableNbspChar, $level * 4);

        foreach ($this->_getMenuIterator($menu) as $menuItem) {
            /**@var  $menuItem \Magento\Backend\Model\Menu\Item */
            if ($menuItem->getAction()) {
                $optionArray[] = [
                    'label' => $paddingString . $menuItem->getTitle(),
                    'value' => $menuItem->getId(),
                ];

                if ($menuItem->hasChildren()) {
                    $this->_createOptions($optionArray, $menuItem->getChildren(), $level + 1);
                }
            } else {
                $children = [];

                if ($menuItem->hasChildren()) {
                    $this->_createOptions($children, $menuItem->getChildren(), $level + 1);
                }

                $optionArray[] = ['label' => $paddingString . $menuItem->getTitle(), 'value' => $children];
            }
        }
    }
}

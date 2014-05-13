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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Backend\Model;

/**
 * Backend menu model
 */
class Menu extends \ArrayObject
{
    /**
     * Name of special logger key for debugging building menu
     */
    const LOGGER_KEY = 'menu-debug';

    /**
     * Path in tree structure
     *
     * @var string
     */
    protected $_path = '';

    /**
     * @var \Magento\Framework\Logger
     */
    protected $_logger;

    /**
     * @param \Magento\Framework\Logger $logger
     * @param string $pathInMenuStructure
     */
    public function __construct(\Magento\Framework\Logger $logger, $pathInMenuStructure = '')
    {
        if ($pathInMenuStructure) {
            $this->_path = $pathInMenuStructure . '/';
        }
        $this->_logger = $logger;
        $this->setIteratorClass('Magento\Backend\Model\Menu\Iterator');
    }

    /**
     * Add child to menu item
     *
     * @param \Magento\Backend\Model\Menu\Item $item
     * @param string $parentId
     * @param int $index
     * @return void
     * @throws \InvalidArgumentException
     */
    public function add(\Magento\Backend\Model\Menu\Item $item, $parentId = null, $index = null)
    {
        if (!is_null($parentId)) {
            $parentItem = $this->get($parentId);
            if ($parentItem === null) {
                throw new \InvalidArgumentException("Item with identifier {$parentId} does not exist");
            }
            $parentItem->getChildren()->add($item, null, $index);
        } else {
            $index = intval($index);
            if (!isset($this[$index])) {
                $this->offsetSet($index, $item);
                $this->_logger->logDebug(
                    sprintf('Add of item with id %s was processed', $item->getId()),
                    self::LOGGER_KEY
                );
            } else {
                $this->add($item, $parentId, $index + 1);
            }
        }
    }

    /**
     * Retrieve menu item by id
     *
     * @param string $itemId
     * @return \Magento\Backend\Model\Menu\Item|null
     */
    public function get($itemId)
    {
        $result = null;
        foreach ($this as $item) {
            /** @var $item \Magento\Backend\Model\Menu\Item */
            if ($item->getId() == $itemId) {
                $result = $item;
                break;
            }

            if ($item->hasChildren() && ($result = $item->getChildren()->get($itemId))) {
                break;
            }
        }
        return $result;
    }

    /**
     * Move menu item
     *
     * @param string $itemId
     * @param string $toItemId
     * @param int $sortIndex
     * @return void
     * @throws \InvalidArgumentException
     */
    public function move($itemId, $toItemId, $sortIndex = null)
    {
        $item = $this->get($itemId);
        if ($item === null) {
            throw new \InvalidArgumentException("Item with identifier {$itemId} does not exist");
        }
        $this->remove($itemId);
        $this->add($item, $toItemId, $sortIndex);
    }

    /**
     * Remove menu item by id
     *
     * @param string $itemId
     * @return bool
     */
    public function remove($itemId)
    {
        $result = false;
        foreach ($this as $key => $item) {
            /** @var $item \Magento\Backend\Model\Menu\Item */
            if ($item->getId() == $itemId) {
                unset($this[$key]);
                $result = true;
                $this->_logger->logDebug(
                    sprintf('Remove on item with id %s was processed', $item->getId()),
                    self::LOGGER_KEY
                );
                break;
            }

            if ($item->hasChildren() && ($result = $item->getChildren()->remove($itemId))) {
                break;
            }
        }
        return $result;
    }

    /**
     * Change order of an item in its parent menu
     *
     * @param string $itemId
     * @param int $position
     * @return bool
     */
    public function reorder($itemId, $position)
    {
        $result = false;
        foreach ($this as $key => $item) {
            /** @var $item \Magento\Backend\Model\Menu\Item */
            if ($item->getId() == $itemId) {
                unset($this[$key]);
                $this->add($item, null, $position);
                $result = true;
                break;
            } elseif ($item->hasChildren() && $result = $item->getChildren()->reorder($itemId, $position)) {
                break;
            }
        }
        return $result;
    }

    /**
     * Check whether provided item is last in list
     *
     * @param \Magento\Backend\Model\Menu\Item $item
     * @return bool
     */
    public function isLast(\Magento\Backend\Model\Menu\Item $item)
    {
        return $this->offsetGet(max(array_keys($this->getArrayCopy())))->getId() == $item->getId();
    }

    /**
     * Find first menu item that user is able to access
     *
     * @return \Magento\Backend\Model\Menu\Item|null
     */
    public function getFirstAvailable()
    {
        $result = null;
        /** @var $item \Magento\Backend\Model\Menu\Item */
        foreach ($this as $item) {
            if ($item->isAllowed() && !$item->isDisabled()) {
                if ($item->hasChildren()) {
                    $result = $item->getChildren()->getFirstAvailable();
                    if (false == is_null($result)) {
                        break;
                    }
                } else {
                    $result = $item;
                    break;
                }
            }
        }
        return $result;
    }

    /**
     * Get parent items by item id
     *
     * @param string $itemId
     * @return \Magento\Backend\Model\Menu\Item[]
     */
    public function getParentItems($itemId)
    {
        $parents = array();
        $this->_findParentItems($this, $itemId, $parents);
        return array_reverse($parents);
    }

    /**
     * Find parent items
     *
     * @param \Magento\Backend\Model\Menu $menu
     * @param string $itemId
     * @param array &$parents
     * @return bool
     */
    protected function _findParentItems($menu, $itemId, &$parents)
    {
        foreach ($menu as $item) {
            /** @var $item \Magento\Backend\Model\Menu\Item */
            if ($item->getId() == $itemId) {
                return true;
            }
            if ($item->hasChildren()) {
                if ($this->_findParentItems($item->getChildren(), $itemId, $parents)) {
                    $parents[] = $item;
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Hack to unset logger instance which cannot be serialized
     *
     * @return string
     */
    public function serialize()
    {
        $logger = $this->_logger;
        unset($this->_logger);
        $result = parent::serialize();
        $this->_logger = $logger;
        return $result;
    }
}

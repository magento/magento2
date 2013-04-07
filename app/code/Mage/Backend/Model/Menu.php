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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Backend menu model
 */
class Mage_Backend_Model_Menu extends ArrayObject
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
     * @var Mage_Core_Model_Logger
     */
    protected $_logger;

    /**
     * @param Mage_Core_Model_Logger $logger
     * @param string $pathInMenuStructure
     */
    public function __construct(Mage_Core_Model_Logger $logger, $pathInMenuStructure = '')
    {
        if ($pathInMenuStructure) {
            $this->_path = $pathInMenuStructure . '/';
        }
        $this->_logger = $logger;
        $this->setIteratorClass('Mage_Backend_Model_Menu_Iterator');
    }

    /**
     * Add child to menu item
     *
     * @param Mage_Backend_Model_Menu_Item $item
     * @param string $parentId
     * @param int $index
     * @throws InvalidArgumentException
     */
    public function add(Mage_Backend_Model_Menu_Item $item, $parentId = null, $index = null)
    {
        if (!is_null($parentId)) {
            $parentItem = $this->get($parentId);
            if ($parentItem === null) {
                throw new InvalidArgumentException("Item with identifier {$parentId} does not exist");
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
     * @return Mage_Backend_Model_Menu_Item|null
     */
    public function get($itemId)
    {
        $result = null;
        foreach ($this as $item) {
            /** @var $item Mage_Backend_Model_Menu_Item */
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
     */
    public function move($itemId, $toItemId, $sortIndex = null)
    {
        $item = $this->get($itemId);
        if ($item === null) {
            throw new InvalidArgumentException("Item with identifier {$itemId} does not exist");
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
            /** @var $item Mage_Backend_Model_Menu_Item */
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
            /** @var $item Mage_Backend_Model_Menu_Item */
            if ($item->getId() == $itemId) {
                unset($this[$key]);
                $this->add($item, null, $position);
                $result = true;
                break;
            } else if ($item->hasChildren() && $result = $item->getChildren()->reorder($itemId, $position)) {
                break;
            }
        }
        return $result;
    }

    /**
     * Check whether provided item is last in list
     *
     * @param Mage_Backend_Model_Menu_Item $item
     * @return bool
     */
    public function isLast(Mage_Backend_Model_Menu_Item $item)
    {
        return $this->offsetGet(max(array_keys($this->getArrayCopy())))->getId() == $item->getId();
    }

    /**
     * Find first menu item that user is able to access
     *
     * @return Mage_Backend_Model_Menu_Item|null
     */
    public function getFirstAvailable()
    {
        $result = null;
        /** @var $item Mage_Backend_Model_Menu_Item */
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

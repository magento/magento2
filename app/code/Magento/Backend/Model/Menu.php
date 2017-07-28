<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Model;

use Magento\Backend\Model\Menu\Item;
use Magento\Backend\Model\Menu\Item\Factory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Serialize\SerializerInterface;
use Psr\Log\LoggerInterface;

/**
 * Backend menu model
 *
 * @api
 * @since 2.0.0
 */
class Menu extends \ArrayObject
{
    /**
     * Path in tree structure
     *
     * @var string
     * @since 2.0.0
     */
    protected $_path = '';

    /**
     * @var LoggerInterface
     * @since 2.0.0
     */
    protected $_logger;

    /**
     * @var Factory
     * @since 2.2.0
     */
    private $menuItemFactory;

    /**
     * @var SerializerInterface
     * @since 2.2.0
     */
    private $serializer;

    /**
     * Menu constructor
     *
     * @param LoggerInterface $logger
     * @param string $pathInMenuStructure
     * @param Factory|null $menuItemFactory
     * @param SerializerInterface|null $serializer
     * @since 2.0.0
     */
    public function __construct(
        LoggerInterface $logger,
        $pathInMenuStructure = '',
        Factory $menuItemFactory = null,
        SerializerInterface $serializer = null
    ) {
        if ($pathInMenuStructure) {
            $this->_path = $pathInMenuStructure . '/';
        }
        $this->_logger = $logger;
        $this->setIteratorClass(\Magento\Backend\Model\Menu\Iterator::class);
        $this->menuItemFactory = $menuItemFactory ?: ObjectManager::getInstance()
            ->create(Factory::class);
        $this->serializer = $serializer ?: ObjectManager::getInstance()->create(SerializerInterface::class);
    }

    /**
     * Add child to menu item
     *
     * @param Item $item
     * @param string $parentId
     * @param int $index
     * @return void
     * @throws \InvalidArgumentException
     * @since 2.0.0
     */
    public function add(Item $item, $parentId = null, $index = null)
    {
        if ($parentId !== null) {
            $parentItem = $this->get($parentId);
            if ($parentItem === null) {
                throw new \InvalidArgumentException("Item with identifier {$parentId} does not exist");
            }
            $parentItem->getChildren()->add($item, null, $index);
        } else {
            $index = intval($index);
            if (!isset($this[$index])) {
                $this->offsetSet($index, $item);
                $this->_logger->info(
                    sprintf('Add of item with id %s was processed', $item->getId())
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
     * @return Item|null
     * @since 2.0.0
     */
    public function get($itemId)
    {
        $result = null;
        /** @var Item $item */
        foreach ($this as $item) {
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
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function remove($itemId)
    {
        $result = false;
        /** @var Item $item */
        foreach ($this as $key => $item) {
            if ($item->getId() == $itemId) {
                unset($this[$key]);
                $result = true;
                $this->_logger->info(
                    sprintf('Remove on item with id %s was processed', $item->getId())
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
     * @since 2.0.0
     */
    public function reorder($itemId, $position)
    {
        $result = false;
        /** @var Item $item */
        foreach ($this as $key => $item) {
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
     * @param Item $item
     * @return bool
     * @since 2.0.0
     */
    public function isLast(Item $item)
    {
        return $this->offsetGet(max(array_keys($this->getArrayCopy())))->getId() == $item->getId();
    }

    /**
     * Find first menu item that user is able to access
     *
     * @return Item|null
     * @since 2.0.0
     */
    public function getFirstAvailable()
    {
        $result = null;
        /** @var Item $item */
        foreach ($this as $item) {
            if ($item->isAllowed() && !$item->isDisabled()) {
                if ($item->hasChildren()) {
                    $result = $item->getChildren()->getFirstAvailable();
                    if (false == ($result === null)) {
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
     * @return Item[]
     * @since 2.0.0
     */
    public function getParentItems($itemId)
    {
        $parents = [];
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
     * @since 2.0.0
     */
    protected function _findParentItems($menu, $itemId, &$parents)
    {
        /** @var Item $item */
        foreach ($menu as $item) {
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
     * Serialize menu
     *
     * @return string
     * @since 2.0.0
     */
    public function serialize()
    {
        return $this->serializer->serialize($this->toArray());
    }

    /**
     * Get menu data represented as an array
     *
     * @return array
     * @since 2.2.0
     */
    public function toArray()
    {
        $data = [];
        foreach ($this as $item) {
            $data[] = $item->toArray();
        }
        return $data;
    }

    /**
     * Unserialize menu
     *
     * @param string $serialized
     * @return void
     * @since 2.2.0
     */
    public function unserialize($serialized)
    {
        $data = $this->serializer->unserialize($serialized);
        $this->populateFromArray($data);
    }

    /**
     * Populate the menu with data from array
     *
     * @param array $data
     * @return void
     * @since 2.2.0
     */
    public function populateFromArray(array $data)
    {
        $items = [];
        foreach ($data as $itemData) {
            $item = $this->menuItemFactory->create($itemData);
            $items[] = $item;
        }
        $this->exchangeArray($items);
    }
}

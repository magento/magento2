<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Publisher\Config\PublisherConfigItem;

use Magento\Framework\MessageQueue\Publisher\Config\Data;
use Magento\Framework\MessageQueue\Publisher\Config\PublisherConfigItem;
use Magento\Framework\MessageQueue\Publisher\Config\PublisherConfigItemFactory;

/**
 * Publisher config data iterator.
 */
class Iterator implements \Iterator, \ArrayAccess
{
    /**
     * Publisher config item.
     *
     * @var PublisherConfigItem
     */
    private $object;

    /**
     * Config data.
     *
     * @var array
     */
    private $data;

    /**
     * Initialize dependencies.
     *
     * @param Data $configData
     * @param PublisherConfigItemFactory $itemFactory
     */
    public function __construct(Data $configData, PublisherConfigItemFactory $itemFactory)
    {
        $this->data = $configData->get();
        $this->object = $itemFactory->create();
        $this->rewind();
    }

    /**
     * Get current item.
     *
     * @return PublisherConfigItem
     */
    #[\ReturnTypeWillChange]
    public function current()
    {
        return $this->object;
    }

    /**
     * @inheritdoc
     */
    #[\ReturnTypeWillChange]
    public function next()
    {
        next($this->data);
        if (current($this->data)) {
            $this->initObject(current($this->data));
            if ($this->current()->isDisabled()) {
                $this->next();
            }
        }
    }

    /**
     * Initialize object.
     *
     * @param array $data
     * @return void
     */
    private function initObject(array $data)
    {
        $this->object->setData($data);
    }

    /**
     * @inheritdoc
     */
    #[\ReturnTypeWillChange]
    public function key()
    {
        key($this->data);
    }

    /**
     * @inheritdoc
     */
    #[\ReturnTypeWillChange]
    public function valid()
    {
        return (bool)current($this->data);
    }

    /**
     * @inheritdoc
     */
    #[\ReturnTypeWillChange]
    public function rewind()
    {
        reset($this->data);
        if (current($this->data)) {
            $this->initObject(current($this->data));
            if ($this->current()->isDisabled()) {
                $this->next();
            }
        }
    }

    /**
     * @inheritdoc
     */
    #[\ReturnTypeWillChange]
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->data);
    }

    /**
     * @inheritdoc
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        if (!$this->offsetExists($offset) || $this->data[$offset]['disabled'] == true) {
            return null;
        }
        $item = clone $this->object;
        $item->setData($this->data[$offset]);
        return $item;
    }

    /**
     * @inheritdoc
     */
    #[\ReturnTypeWillChange]
    public function offsetSet($offset, $value)
    {
        $this->data[$offset] = $value;
    }

    /**
     * @inheritdoc
     */
    #[\ReturnTypeWillChange]
    public function offsetUnset($offset)
    {
        unset($this->data[$offset]);
    }
}

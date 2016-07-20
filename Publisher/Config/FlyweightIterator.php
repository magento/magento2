<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\MessageQueue\Publisher\Config;

/**
 * Publisher config data iterator.
 */
class FlyweightIterator implements \Iterator, \ArrayAccess
{
    /**
     * Publisher config item.
     *
     * @var PublisherConfigItemInterface
     */
    private $flyweight;

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
        $this->flyweight = $itemFactory->create();
    }

    /**
     * Get current item.
     *
     * @return PublisherConfigItemInterface
     */
    public function current()
    {
        return $this->flyweight;
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        next($this->data);
        if (current($this->data)) {
            $this->initFlyweight(current($this->data));
            if ($this->current()->isDisabled()) {
                $this->next();
            }
        }
    }

    /**
     * Initialize Flyweight object.
     *
     * @param array $data
     * @return void
     */
    private function initFlyweight(array $data)
    {
        $this->flyweight->setData($data);
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        key($this->data);
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        return (bool)current($this->data);
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        reset($this->data);
        if (current($this->data)) {
            $this->initFlyweight(current($this->data));
            if ($this->current()->isDisabled()) {
                $this->next();
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->data);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($offset)
    {
        if (!$this->offsetExists($offset) || $this->data[$offset]['disabled'] == true) {
            return null;
        }
        $item = clone $this->flyweight;
        $item->setData($this->data[$offset]);
        return $item;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value)
    {
        $this->data[$offset] = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset)
    {
        unset($this->data[$offset]);
    }
}

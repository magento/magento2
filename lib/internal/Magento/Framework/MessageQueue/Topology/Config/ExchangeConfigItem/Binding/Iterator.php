<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Topology\Config\ExchangeConfigItem\Binding;

use Magento\Framework\MessageQueue\Topology\Config\ExchangeConfigItem\Binding;
use Magento\Framework\MessageQueue\Topology\Config\ExchangeConfigItem\BindingFactory;

/**
 * Exchange binding config data iterator.
 */
class Iterator implements \Iterator, \ArrayAccess
{
    /**
     * Exchange binding config.
     *
     * @var Binding
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
     * @param BindingFactory $itemFactory
     */
    public function __construct(BindingFactory $itemFactory)
    {
        $this->object = $itemFactory->create();
    }

    /**
     * Set iterator data.
     *
     * @param array $data
     * @return void
     */
    public function setData(array $data)
    {
        $this->data = $data;
        $this->rewind();
    }

    /**
     * Get current item.
     *
     * @return Binding
     */
    public function current()
    {
        return $this->object;
    }

    /**
     * {@inheritdoc}
     */
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
            $this->initObject(current($this->data));
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
        $item = clone $this->object;
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

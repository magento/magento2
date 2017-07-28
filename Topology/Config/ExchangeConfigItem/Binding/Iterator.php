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
 * @since 2.2.0
 */
class Iterator implements \Iterator, \ArrayAccess
{
    /**
     * Exchange binding config.
     *
     * @var Binding
     * @since 2.2.0
     */
    private $object;

    /**
     * Config data.
     *
     * @var array
     * @since 2.2.0
     */
    private $data;

    /**
     * Initialize dependencies.
     *
     * @param BindingFactory $itemFactory
     * @since 2.2.0
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
     * @since 2.2.0
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
     * @since 2.2.0
     */
    public function current()
    {
        return $this->object;
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
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
     * @since 2.2.0
     */
    private function initObject(array $data)
    {
        $this->object->setData($data);
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function key()
    {
        key($this->data);
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function valid()
    {
        return (bool)current($this->data);
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
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
     * @since 2.2.0
     */
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->data);
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
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
     * @since 2.2.0
     */
    public function offsetSet($offset, $value)
    {
        $this->data[$offset] = $value;
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function offsetUnset($offset)
    {
        unset($this->data[$offset]);
    }
}

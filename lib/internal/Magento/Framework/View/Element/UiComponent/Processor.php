<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element\UiComponent;

use Magento\Framework\View\Element\UiComponentInterface;

/**
 * Class Processor
 */
class Processor implements PoolInterface, SubjectInterface
{
    /**
     * @var UiComponentInterface[]
     */
    protected $components = [];

    /**
     * Array of observers
     *
     * [
     * 'component_type1' => ObserverInterface[],
     * 'component_type2' => ObserverInterface[],
     * ]
     *
     * @var array
     */
    protected $observers = [];


    /**
     * @inheritDoc
     */
    public function register(UiComponentInterface $component)
    {
        $this->components[] = $component;
    }

    /**
     * @inheritDoc
     */
    public function getComponents()
    {
        return $this->components;
    }

    /**
     * @inheritDoc
     */
    public function attach($type, ObserverInterface $observer)
    {
        $this->observers[$type][] = $observer;
    }

    /**
     * @inheritDoc
     */
    public function detach($type, ObserverInterface $observer)
    {
        if (!isset($this->observers[$type])) {
            return;
        }

        $key = array_search($observer, $this->observers[$type], true);
        if ($key !== false) {
            unset($this->observers[$type][$key]);
        }
    }

    /**
     * @inheritDoc
     */
    public function notify($type)
    {
        $componentType = $this->normalizeType($type);
        if (!isset($this->observers[$componentType])) {
            return;
        }

        /** @var UiComponentInterface $component */
        foreach ($this->getComponents() as $component) {
            if ($component->getComponentName() != $type) {
                continue;
            }

            /** @var ObserverInterface $observer */
            foreach ($this->observers[$componentType] as $observer) {
                $observer->update($component);
            }
        }
    }

    /**
     * Normalize type to component type
     *
     * @param string $type
     * @return string
     */
    protected function normalizeType($type)
    {
        $componentType = (strpos($type, '.') !== false) ? substr($type, 0, strpos($type, '.')) : $type;
        return $componentType;
    }
}

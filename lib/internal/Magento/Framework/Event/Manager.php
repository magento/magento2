<?php
/**
 * Event manager
 * Used to dispatch global events
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Event;

/**
 * Class \Magento\Framework\Event\Manager
 *
 * @since 2.0.0
 */
class Manager implements ManagerInterface
{
    /**
     * Events cache
     *
     * @var array
     * @since 2.0.0
     */
    protected $_events = [];

    /**
     * Event invoker
     *
     * @var InvokerInterface
     * @since 2.0.0
     */
    protected $_invoker;

    /**
     * Event config
     *
     * @var ConfigInterface
     * @since 2.0.0
     */
    protected $_eventConfig;

    /**
     * @param InvokerInterface $invoker
     * @param ConfigInterface $eventConfig
     * @since 2.0.0
     */
    public function __construct(InvokerInterface $invoker, ConfigInterface $eventConfig)
    {
        $this->_invoker = $invoker;
        $this->_eventConfig = $eventConfig;
    }

    /**
     * Dispatch event
     *
     * Calls all observer callbacks registered for this event
     * and multiple observers matching event name pattern
     *
     * @param string $eventName
     * @param array $data
     * @return void
     * @since 2.0.0
     */
    public function dispatch($eventName, array $data = [])
    {
        $eventName = mb_strtolower($eventName);
        \Magento\Framework\Profiler::start('EVENT:' . $eventName, ['group' => 'EVENT', 'name' => $eventName]);
        foreach ($this->_eventConfig->getObservers($eventName) as $observerConfig) {
            $event = new \Magento\Framework\Event($data);
            $event->setName($eventName);

            $wrapper = new Observer();
            $wrapper->setData(array_merge(['event' => $event], $data));

            \Magento\Framework\Profiler::start('OBSERVER:' . $observerConfig['name']);
            $this->_invoker->dispatch($observerConfig, $wrapper);
            \Magento\Framework\Profiler::stop('OBSERVER:' . $observerConfig['name']);
        }
        \Magento\Framework\Profiler::stop('EVENT:' . $eventName);
    }
}

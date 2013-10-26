<?php
/**
 * Event manager
 * Used to dispatch global events
 *
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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Event;

class Manager implements ManagerInterface
{
    /**
     * Events cache
     *
     * @var array
     */
    protected $_events = array();

    /**
     * Event invoker
     *
     * @var \Magento\Event\InvokerInterface
     */
    protected $_invoker;

    /**
     * Event config
     *
     * @var \Magento\Event\ConfigInterface
     */
    protected $_eventConfig;

    /**
     * Magento event factory
     *
     * @var \Magento\EventFactory
     */
    protected $_eventFactory;

    /**
     * Magento event observer factory
     *
     * @var \Magento\Event\WrapperFactory
     */
    protected $wrapperFactory;

    /**
     * @param InvokerInterface $invoker
     * @param ConfigInterface $eventConfig
     * @param \Magento\EventFactory $eventFactory
     * @param WrapperFactory $wrapperFactory
     */
    public function __construct(
        \Magento\Event\InvokerInterface $invoker,
        \Magento\Event\ConfigInterface $eventConfig,
        \Magento\EventFactory $eventFactory,
        \Magento\Event\WrapperFactory $wrapperFactory
    ) {
        $this->_invoker = $invoker;
        $this->_eventConfig = $eventConfig;
        $this->_eventFactory = $eventFactory;
        $this->wrapperFactory = $wrapperFactory;
    }

    /**
     * Dispatch event
     *
     * Calls all observer callbacks registered for this event
     * and multiple observers matching event name pattern
     *
     * @param string $eventName
     * @param array $data
     */
    public function dispatch($eventName, array $data = array())
    {
        \Magento\Profiler::start('EVENT:' . $eventName, array('group' => 'EVENT', 'name' => $eventName));
        foreach ($this->_eventConfig->getObservers($eventName) as $observerConfig) {
            /** @var $event \Magento\Event */
            $event = $this->_eventFactory->create(array('data' => $data));
            $event->setName($eventName);

            /** @var $wrapper \Magento\Event\Observer */
            $wrapper = $this->wrapperFactory->create();
            $wrapper->setData(array_merge(array('event' => $event), $data));

            \Magento\Profiler::start('OBSERVER:' . $observerConfig['name']);
            $this->_invoker->dispatch($observerConfig, $wrapper);
            \Magento\Profiler::stop('OBSERVER:' .  $observerConfig['name']);
        }
        \Magento\Profiler::stop('EVENT:' . $eventName);
    }
}

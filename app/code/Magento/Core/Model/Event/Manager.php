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
 * obtain it through the world-wide-web, please send an e-mail
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
namespace Magento\Core\Model\Event;

class Manager
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
     * @var \Magento\Core\Model\Event\InvokerInterface
     */
    protected $_invoker;

    /**
     * Event config
     *
     * @var \Magento\Core\Model\Event\ConfigInterface
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
     * @var \Magento\Event\ObserverFactory
     */
    protected $_eventObserverFactory;

    /**
     * @param \Magento\Core\Model\Event\InvokerInterface $invoker
     * @param \Magento\Core\Model\Event\ConfigInterface $eventConfig
     * @param \Magento\EventFactory $eventFactory
     * @param \Magento\Event\ObserverFactory $eventObserverFactory
     */
    public function __construct(
        \Magento\Core\Model\Event\InvokerInterface $invoker,
        \Magento\Core\Model\Event\ConfigInterface $eventConfig,
        \Magento\EventFactory $eventFactory,
        \Magento\Event\ObserverFactory $eventObserverFactory
    ) {
        $this->_invoker = $invoker;
        $this->_eventConfig = $eventConfig;
        $this->_eventFactory = $eventFactory;
        $this->_eventObserverFactory = $eventObserverFactory;
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

            /** @var $observer \Magento\Event\Observer */
            $observer = $this->_eventObserverFactory->create();
            $observer->setData(array_merge(array('event' => $event), $data));

            \Magento\Profiler::start('OBSERVER:' . $observerConfig['name']);
            $this->_invoker->dispatch($observerConfig, $observer);
            \Magento\Profiler::stop('OBSERVER:' .  $observerConfig['name']);
        }
        \Magento\Profiler::stop('EVENT:' . $eventName);
    }
}

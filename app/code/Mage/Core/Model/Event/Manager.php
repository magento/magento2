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
class Mage_Core_Model_Event_Manager
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
     * @var Mage_Core_Model_Event_InvokerInterface
     */
    protected $_invoker;

    /**
     * Event config
     *
     * @var Mage_Core_Model_Event_Config
     */
    protected $_eventConfig;

    /**
     * Varien event factory
     *
     * @var Varien_EventFactory
     */
    protected $_eventFactory;

    /**
     * Varien event observer factory
     *
     * @var Varien_Event_ObserverFactory
     */
    protected $_eventObserverFactory;

    /**
     * @param Mage_Core_Model_Event_InvokerInterface $invoker
     * @param Mage_Core_Model_Event_Config $eventConfig
     * @param Varien_EventFactory $eventFactory
     * @param Varien_Event_ObserverFactory $eventObserverFactory
     */
    public function __construct(
        Mage_Core_Model_Event_InvokerInterface $invoker,
        Mage_Core_Model_Event_Config $eventConfig,
        Varien_EventFactory $eventFactory,
        Varien_Event_ObserverFactory $eventObserverFactory
    ) {
        $this->_invoker = $invoker;
        $this->_eventConfig = $eventConfig;
        $this->_eventFactory = $eventFactory;
        $this->_eventObserverFactory = $eventObserverFactory;
        $this->addEventArea(Mage_Core_Model_App_Area::AREA_GLOBAL);
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
        Magento_Profiler::start('EVENT:' . $eventName, array('group' => 'EVENT', 'name' => $eventName));
        foreach ($this->_events as $areaEvents) {
            if (!isset($areaEvents[$eventName])) {
                continue;
            }

            /** @var $event Varien_Event */
            $event = $this->_eventFactory->create(array('data' => $data));
            $event->setName($eventName);
            /** @var $observer Varien_Event_Observer */
            $observer = $this->_eventObserverFactory->create();

            foreach ($areaEvents[$eventName] as $obsName => $obsConfiguration) {
                $observer->setData(array_merge(array('event' => $event), $data));

                Magento_Profiler::start('OBSERVER:' . $obsName);
                $this->_invoker->dispatch($obsConfiguration, $observer);
                Magento_Profiler::stop('OBSERVER:' . $obsName);
            }
        }
        Magento_Profiler::stop('EVENT:' . $eventName);
    }

    /**
     * Add event area
     *
     * @param string $area
     * @return Mage_Core_Model_Event_Manager
     */
    public function addEventArea($area)
    {
        if (!isset($this->_events[$area])) {
            Magento_Profiler::start('config_event_' . $area);
            $this->_events[$area] = array();
            $this->_eventConfig->populate($this, $area);
            Magento_Profiler::stop('config_event_' . $area);
        }
        return $this;
    }

    /**
     * Add event observer
     *
     * @param string $area
     * @param string $eventName
     * @param array $observers example array('observerName' => array('type' => ..., 'model' => ..., 'method' => ... ),)
     */
    public function addObservers($area, $eventName, array $observers)
    {
        if (!isset($this->_events[$area])) {
             $this->addEventArea($area);
        }

        $existingObservers = array();
        if (isset($this->_events[$area][$eventName])) {
            $existingObservers = $this->_events[$area][$eventName];
        }
        $this->_events[$area][$eventName] = array_merge($existingObservers, $observers);
    }
}

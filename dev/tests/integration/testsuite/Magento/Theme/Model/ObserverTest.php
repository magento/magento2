<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test theme observer
 */
namespace Magento\Theme\Model;

class ObserverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Event\Observer
     */
    protected $_eventObserver;

    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    protected $_objectManager;

    protected function setUp()
    {
        $this->_objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->_eventObserver = $this->_createEventObserverForThemeRegistration();
    }

    /**
     * Theme registration test
     *
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testThemeRegistration()
    {
        $pattern = 'path_pattern';

        $this->_eventObserver->getEvent()->setPathPattern($pattern);

        $themeRegistration = $this->getMock(
            'Magento\Theme\Model\Theme\Registration',
            ['register'],
            [
                $this->_objectManager->create('Magento\Theme\Model\Resource\Theme\Data\CollectionFactory'),
                $this->_objectManager->create('Magento\Theme\Model\Theme\Data\Collection'),
                $this->_objectManager->create('Magento\Framework\Filesystem')
            ]
        );
        $themeRegistration->expects($this->once())->method('register')->with($this->equalTo($pattern));
        $this->_objectManager->addSharedInstance($themeRegistration, 'Magento\Theme\Model\Theme\Registration');

        /** @var $observer \Magento\Theme\Model\Observer */
        $observer = $this->_objectManager->create('Magento\Theme\Model\Observer');
        $observer->themeRegistration($this->_eventObserver);
    }

    /**
     * Create event observer for theme registration
     *
     * @return \Magento\Framework\Event\Observer
     */
    protected function _createEventObserverForThemeRegistration()
    {
        $response = $this->_objectManager->create(
            'Magento\Framework\Object',
            ['data' => ['additional_options' => []]]
        );
        $event = $this->_objectManager->create(
            'Magento\Framework\Event',
            ['data' => ['response_object' => $response]]
        );
        return $this->_objectManager->create(
            'Magento\Framework\Event\Observer',
            ['data' => ['event' => $event]]
        );
    }
}

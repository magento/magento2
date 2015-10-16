<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test theme observer
 */
namespace Magento\Theme\Observer;

class ThemeRegistrationObserverTest extends \PHPUnit_Framework_TestCase
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
        $themeRegistration = $this->getMock(
            'Magento\Theme\Model\Theme\Registration',
            ['register'],
            [
                $this->_objectManager->create('Magento\Theme\Model\ResourceModel\Theme\Data\CollectionFactory'),
                $this->_objectManager->create('Magento\Theme\Model\Theme\Data\Collection'),
                $this->_objectManager->create('Magento\Framework\Filesystem')
            ]
        );
        $themeRegistration->expects($this->once())->method('register');
        $this->_objectManager->addSharedInstance($themeRegistration, 'Magento\Theme\Model\Theme\Registration');

        /** @var $observer \Magento\Theme\Observer\ThemeRegistrationObserver */
        $observer = $this->_objectManager->create('Magento\Theme\Observer\ThemeRegistrationObserver');
        $observer->execute($this->_eventObserver);
    }

    /**
     * Create event observer for theme registration
     *
     * @return \Magento\Framework\Event\Observer
     */
    protected function _createEventObserverForThemeRegistration()
    {
        $response = $this->_objectManager->create(
            'Magento\Framework\DataObject',
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

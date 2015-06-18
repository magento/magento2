<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model\App;

use Magento\TestFramework\Helper\Bootstrap;

class EmulationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Store\Model\App\Emulation
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Store\Model\App\Emulation');
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Store/_files/core_fixturestore.php
     * @covers \Magento\Store\Model\App\Emulation::startEnvironmentEmulation
     * @covers \Magento\Store\Model\App\Emulation::stopEnvironmentEmulation
     */
    public function testEnvironmentEmulation()
    {
        // Using the fixture store because if we pass the default store code to startEnvironmentEmulation, it won't
        // run emulation since the default store is already loaded, so it thinks it doesn't need to run emulation
        $storeCode = 'fixturestore';

        // Load adminhtml area so that we can emulate the frontend area and ensure that it gets emulated properly
        Bootstrap::getInstance()
            ->loadArea(\Magento\Backend\App\Area\FrontNameResolver::AREA_CODE);

        $design = Bootstrap::getObjectManager()
            ->get('Magento\Framework\View\DesignInterface');

        $this->_model->startEnvironmentEmulation($storeCode, \Magento\Framework\App\Area::AREA_FRONTEND);
        $this->assertEquals(\Magento\Framework\App\Area::AREA_FRONTEND, $design->getArea());
        $this->_model->stopEnvironmentEmulation();
        $this->assertEquals(\Magento\Backend\App\Area\FrontNameResolver::AREA_CODE, $design->getArea());
    }

    /**
     * @covers \Magento\Store\Model\App\Emulation::startEnvironmentEmulation
     * @covers \Magento\Store\Model\App\Emulation::stopEnvironmentEmulation
     * @param string $originalArea
     * @param string $emulatedArea
     * @dataProvider forceEnvironmentEmulationEmulatesAreaDataProvider
     */
    public function testForceEnvironmentEmulationEmulatesArea($originalArea, $emulatedArea)
    {
        Bootstrap::getInstance()
            ->loadArea($originalArea);

        $this->_model->startEnvironmentEmulation(
            1,
            $emulatedArea,
            true
        );

        $design = Bootstrap::getObjectManager()
            ->get('Magento\Theme\Model\View\Design');
        $this->assertEquals($emulatedArea, $design->getArea());
        $this->_model->stopEnvironmentEmulation();
        $this->assertEquals($originalArea, $design->getArea());
    }

    public function forceEnvironmentEmulationEmulatesAreaDataProvider()
    {
        return [
            'Emulate frontend when in adminhtml context' => [
                \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE,
                \Magento\Framework\App\Area::AREA_FRONTEND,
            ],
            'Emulate adminhtml when in frontend context' => [
                \Magento\Framework\App\Area::AREA_FRONTEND,
                \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE,
            ],
        ];
    }
}

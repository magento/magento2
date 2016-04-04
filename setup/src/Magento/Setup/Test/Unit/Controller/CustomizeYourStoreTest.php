<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Controller;

use \Magento\Setup\Controller\CustomizeYourStore;

class CustomizeYourStoreTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Setup\Controller\CustomizeYourStore
     */
    private $controller;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Setup\SampleData\State
     */
    private $sampleDataState;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Setup\Lists
     */
    private $lists;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\ObjectManager
     */
    private $objectManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Module\FullModuleList
     */
    private $moduleList;

    public function setup()
    {
        $objectManagerProvider = $this->getMock('Magento\Setup\Model\ObjectManagerProvider', [], [], '', false);
        $this->objectManager = $this->getMock('Magento\Framework\App\ObjectManager', [], [], '', false);
        $objectManagerProvider->expects($this->any())->method('get')->willReturn($this->objectManager);
        $this->sampleDataState = $this->getMock(
            'Magento\Framework\Setup\SampleData\State',
            [],
            [],
            '',
            false
        );
        $this->lists = $this->getMock('\Magento\Framework\Setup\Lists', [], [], '', false);
        $this->moduleList = $this->getMock('Magento\Framework\Module\FullModuleList', [], [], '', false);
        $this->controller = new CustomizeYourStore($this->moduleList, $this->lists, $objectManagerProvider);
    }

    /**
     * @param array $expected
     * @param $withSampleData
     *
     * @dataProvider indexActionDataProvider
     */
    public function testIndexAction($expected, $withSampleData)
    {
        if ($withSampleData) {
            $this->moduleList->expects($this->once())->method('has')->willReturn(true);
            $this->objectManager->expects($this->once())->method('get')->willReturn($this->sampleDataState);
            $this->sampleDataState->expects($this->once())->method('isInstalled')
                ->willReturn($expected['isSampleDataInstalled']);
            $this->sampleDataState->expects($this->once())->method('hasError')
                ->willReturn($expected['isSampleDataErrorInstallation']);
        } else {
            $this->moduleList->expects($this->once())->method('has')->willReturn(false);
            $this->objectManager->expects($this->never())->method('get');
        }
        $this->lists->expects($this->once())->method('getTimezoneList')->willReturn($expected['timezone']);
        $this->lists->expects($this->once())->method('getCurrencyList')->willReturn($expected['currency']);
        $this->lists->expects($this->once())->method('getLocaleList')->willReturn($expected['language']);

        $viewModel = $this->controller->indexAction();

        $this->assertInstanceOf('Zend\View\Model\ViewModel', $viewModel);
        $this->assertTrue($viewModel->terminate());

        $variables = $viewModel->getVariables();
        $this->assertArrayHasKey('timezone', $variables);
        $this->assertArrayHasKey('currency', $variables);
        $this->assertArrayHasKey('language', $variables);
        $this->assertSame($expected, $variables);
    }

    /**
     * @return array
     */
    public function indexActionDataProvider()
    {
        $timezones = ['timezone' => ['America/New_York'=>'EST', 'America/Chicago' => 'CST']];
        $currency = ['currency' => ['USD'=>'US Dollar', 'EUR' => 'Euro']];
        $language = ['language' => ['en_US'=>'English (USA)', 'en_UK' => 'English (UK)']];
        $sampleData = [
            'isSampleDataInstalled' => false,
            'isSampleDataErrorInstallation' => false
        ];

        return [
            'with_all_data' => [array_merge($timezones, $currency, $language, $sampleData), true],
            'no_currency_data' => [array_merge($timezones, ['currency' => null], $language, $sampleData), true],
            'no_timezone_data' => [array_merge(['timezone' => null], $currency, $language, $sampleData), true],
            'no_language_data' => [array_merge($timezones, $currency, ['language' => null], $sampleData), true],
            'empty_currency_data' => [array_merge($timezones, ['currency' => []], $language, $sampleData), true],
            'empty_timezone_data' => [array_merge(['timezone' => []], $currency, $language, $sampleData), true],
            'empty_language_data' => [array_merge($timezones, $currency, ['language' => []], $sampleData), true],
            'no_sample_data' => [array_merge($timezones, $currency, $language, $sampleData), false],
        ];
    }

    public function testDefaultTimeZoneAction()
    {
        $jsonModel = $this->controller->defaultTimeZoneAction();
        $this->assertInstanceOf('Zend\View\Model\JsonModel', $jsonModel);
        $this->assertArrayHasKey('defaultTimeZone', $jsonModel->getVariables());
    }
}

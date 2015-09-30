<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
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
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\SampleData\Model\SampleData
     */
    private $sampleData;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Setup\Model\Lists
     */
    private $lists;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\ObjectManager
     */
    private $objectManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Module\ModuleList
     */
    private $moduleList;

    public function setup()
    {
        $objectManagerProvider = $this->getMock('Magento\Setup\Model\ObjectManagerProvider', [], [], '', false);
        $this->objectManager = $this->getMock('Magento\Framework\App\ObjectManager', [], [], '', false);
        $objectManagerProvider->expects($this->any())->method('get')->willReturn($this->objectManager);
        $this->sampleData = $this->getMock(
            'Magento\SampleData\Model\SampleData',
            ['isInstalledSuccessfully', 'isInstallationError'],
            [],
            '',
            false
        );
        $this->lists = $this->getMock('\Magento\Framework\Setup\Lists', [], [], '', false);
        $this->moduleList = $this->getMock('Magento\Framework\Module\ModuleList', [], [], '', false);
        $this->controller = new CustomizeYourStore($this->moduleList, $this->lists, $objectManagerProvider);
    }

    /**
     * @param array $expected
     *
     * @dataProvider indexActionDataProvider
     */
    public function testIndexAction($expected)
    {
        if ($expected['isSampledataEnabled']) {
            $this->moduleList->expects($this->once())->method('has')->willReturn(true);
            $this->objectManager->expects($this->once())->method('get')->willReturn($this->sampleData);
            $this->sampleData->expects($this->once())->method('isInstalledSuccessfully')
                ->willReturn($expected['isSampleDataInstalled']);
            $this->sampleData->expects($this->once())->method('isInstallationError')
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
            'isSampledataEnabled' => false,
            'isSampleDataInstalled' => false,
            'isSampleDataErrorInstallation' => false
        ];
        $sampleDataTrue = array_merge($sampleData, ['isSampledataEnabled' => true]);
        $sampleDataFalse = array_merge($sampleData, ['isSampledataEnabled' => false]);

        return [
            'with_all_data' => [array_merge($timezones, $currency, $language, $sampleDataTrue)],
            'no_currency_data' => [array_merge($timezones, ['currency' => null], $language, $sampleDataTrue)],
            'no_timezone_data' => [array_merge(['timezone' => null], $currency, $language, $sampleDataTrue)],
            'no_language_data' => [array_merge($timezones, $currency, ['language' => null], $sampleDataTrue)],
            'empty_currency_data' => [array_merge($timezones, ['currency' => []], $language, $sampleDataTrue)],
            'empty_timezone_data' => [array_merge(['timezone' => []], $currency, $language, $sampleDataTrue)],
            'empty_language_data' => [array_merge($timezones, $currency, ['language' => []], $sampleDataTrue)],
            'false_sample_data' => [array_merge($timezones, $currency, $language, $sampleDataFalse)],
            'no_sample_data' => [array_merge($timezones, $currency, $language, $sampleData)],
        ];
    }

    public function testDefaultTimeZoneAction()
    {
        $jsonModel = $this->controller->defaultTimeZoneAction();
        $this->assertInstanceOf('Zend\View\Model\JsonModel', $jsonModel);
        $this->assertArrayHasKey('defaultTimeZone', $jsonModel->getVariables());
    }
}

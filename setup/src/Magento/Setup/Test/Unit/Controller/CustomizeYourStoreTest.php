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
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Setup\Model\SampleData
     */
    private $sampleData;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Setup\Model\Lists
     */
    private $lists;

    public function setup()
    {
        $this->sampleData = $this->getMock('\Magento\Setup\Model\SampleData', [], [], '', false);
        $this->lists = $this->getMock('\Magento\Framework\Setup\Lists', [], [], '', false);
        $this->controller = new CustomizeYourStore($this->lists, $this->sampleData);
    }

    /**
     * @param array $expected
     *
     * @dataProvider indexActionDataProvider
     */
    public function testIndexAction($expected)
    {
        $this->sampleData->expects($this->once())->method('isDeployed')->willReturn($expected['isSampledataEnabled']);
        $this->sampleData->expects($this->once())->method('isInstalledSuccessfully')
            ->willReturn($expected['isSampleDataInstalled']);
        $this->sampleData->expects($this->once())->method('isInstallationError')
            ->willReturn($expected['isSampleDataErrorInstallation']);
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
            'isSampledataEnabled' => null,
            'isSampleDataInstalled' => null,
            'isSampleDataErrorInstallation' => null
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

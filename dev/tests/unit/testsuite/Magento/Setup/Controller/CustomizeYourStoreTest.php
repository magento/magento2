<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Controller;

class CustomizeYourStoreTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param array $expected
     *
     * @dataProvider indexActionDataProvider
     */
    public function testIndexAction($expected)
    {
        $sampleData = $this->getMock('\Magento\Setup\Model\SampleData', [], [], '', false);
        $lists = $this->getMock('\Magento\Setup\Model\Lists', [], [], '', false);
        $modules = $this->getMock('\Magento\Setup\Model\ModuleStatus', [], [], '', false);
        $controller = new CustomizeYourStore($lists, $sampleData, $modules);

        $modules->expects($this->once())->method('getAllModules')->willReturn($expected['modules']);
        $sampleData->expects($this->once())->method('isDeployed')->willReturn($expected['isSampledataEnabled']);
        $lists->expects($this->once())->method('getTimezoneList')->willReturn($expected['timezone']);
        $lists->expects($this->once())->method('getCurrencyList')->willReturn($expected['currency']);
        $lists->expects($this->once())->method('getLocaleList')->willReturn($expected['language']);

        $viewModel = $controller->indexAction();

        $this->assertInstanceOf('Zend\View\Model\ViewModel', $viewModel);
        $this->assertTrue($viewModel->terminate());

        $variables = $viewModel->getVariables();
        $this->assertArrayHasKey('timezone', $variables);
        $this->assertArrayHasKey('currency', $variables);
        $this->assertArrayHasKey('language', $variables);
        $this->assertArrayHasKey('modules', $variables);
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
        $modules = ['modules' => ['module1', 'module2']];
        $noModules = ['modules' => null];
        $key = 'isSampledataEnabled';
        $sampleData = [$key => true];
        $noSampleData = [$key => false];

        return [
            'with_all_data' => [array_merge($timezones, $currency, $language, $sampleData, $noModules)],
            'no_currency_data' => [array_merge($timezones, ['currency' => null], $language, $sampleData, $noModules)],
            'no_timezone_data' => [array_merge(['timezone' => null], $currency, $language, $sampleData, $noModules)],
            'no_language_data' => [array_merge($timezones, $currency, ['language' => null], $sampleData, $noModules)],
            'empty_currency_data' => [array_merge($timezones, ['currency' => []], $language, $sampleData, $noModules)],
            'empty_timezone_data' => [array_merge(['timezone' => []], $currency, $language, $sampleData, $noModules)],
            'empty_language_data' => [array_merge($timezones, $currency, ['language' => []], $sampleData, $noModules)],
            'false_sample_data' => [array_merge($timezones, $currency, $language, $noSampleData, $noModules)],
            'no_sample_data' => [array_merge($timezones, $currency, $language, [$key => null], $noModules)],
            'with_modules'=> [array_merge($timezones, $currency, $language, $sampleData, $modules)],
        ];
    }
}

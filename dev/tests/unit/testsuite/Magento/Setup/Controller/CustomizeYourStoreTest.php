<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
        $controller = new CustomizeYourStore($lists, $sampleData);

        $sampleData->expects($this->once())->method('isDeployed')->will(
            $this->returnValue($expected['isSampledataEnabled']));

        $lists->expects($this->once())->method('getTimezoneList')->will($this->returnValue($expected['timezone']));
        $lists->expects($this->once())->method('getCurrencyList')->will($this->returnValue($expected['currency']));
        $lists->expects($this->once())->method('getLocaleList')->will($this->returnValue($expected['language']));

        $viewModel = $controller->indexAction();

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
        $sampleDataTrue = ['isSampledataEnabled' => true];
        $sampleDataFalse = ['isSampledataEnabled' => false];

        return [
            'with_all_data' => [array_merge($timezones, $currency, $language, $sampleDataTrue)],
            'no_currency_data' => [array_merge($timezones, ['currency' => null], $language, $sampleDataTrue)],
            'no_timezone_data' => [array_merge(['timezone' => null], $currency, $language, $sampleDataTrue)],
            'no_language_data' => [array_merge($timezones, $currency, ['language' => null], $sampleDataTrue)],
            'empty_currency_data' => [array_merge($timezones, ['currency' => []], $language, $sampleDataTrue)],
            'empty_timezone_data' => [array_merge(['timezone' => []], $currency, $language, $sampleDataTrue)],
            'empty_language_data' => [array_merge($timezones, $currency, ['language' => []], $sampleDataTrue)],
            'false_sample_data' => [array_merge($timezones, $currency, $language, $sampleDataFalse)],
            'no_sample_data' => [array_merge($timezones, $currency, $language, ['isSampledataEnabled' => null])],
        ];
    }
}

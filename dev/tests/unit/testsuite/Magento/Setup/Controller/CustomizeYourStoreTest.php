<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Setup\Controller;

class CustomizeYourStoreTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param array $timezone
     * @param array $currency
     * @param array $language
     * @param bool $isSampleDataEnabled
     *
     * @dataProvider indexActionDataProvider
     */
    public function testIndexAction($timezone, $currency, $language, $isSampleDataEnabled)
    {
        $sampleData = $this->getMock('\Magento\Setup\Model\SampleData', [], [], '', false);
        $lists = $this->getMock('\Magento\Setup\Model\Lists', [], [], '', false);
        $controller = new CustomizeYourStore($lists, $sampleData);

        $sampleData->expects($this->once())->method('isDeployed')->with()->will(
            $this->returnValue($isSampleDataEnabled));

        $lists->expects($this->once())->method('getTimezoneList')->with()->will($this->returnValue($timezone));
        $lists->expects($this->once())->method('getCurrencyList')->with()->will($this->returnValue($currency));
        $lists->expects($this->once())->method('getLocaleList')->with()->will($this->returnValue($language));

        $viewModel = $controller->indexAction();

        $this->assertInstanceOf('Zend\View\Model\ViewModel', $viewModel);
        $this->assertTrue($viewModel->terminate());

        $variables = $viewModel->getVariables();
        $this->assertSame($timezone, $variables['timezone']);
        $this->assertSame($currency, $variables['currency']);
        $this->assertSame($language, $variables['language']);
        $this->assertEquals($isSampleDataEnabled, $variables['isSampledataEnabled']);
    }

    public function indexActionDataProvider()
    {
        $timezones = ['America/New_York'=>'EST', 'America/Chicago' => 'CST'];
        $currency = ['USD'=>'US Dollar', 'EUR' => 'Euro'];
        $language = ['en_US'=>'English (USA)', 'en_UK' => 'English (UK)'];

        return [
            'with_all_data' => [$timezones, $currency, $language, true],
            'no_currency_data' => [$timezones, null, $language, true],
            'no_timezone_data' => [null, $currency, $language, true],
            'no_language_data' => [$timezones, $currency, null, true],
            'empty_currency_data' => [$timezones, [], $language, true],
            'empty_timezone_data' => [[], $currency, $language, true],
            'empty_language_data' => [$timezones, $currency, [], true],
            'no_sample_data' => [$timezones, $currency, $language, false],
        ];
    }
}


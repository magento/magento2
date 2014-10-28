<?php
/**
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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Paypal\Model;

use Magento\Paypal\Model\Config as Config;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Config
     */
    protected $_model;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Core\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_coreData;

    protected function setUp()
    {
        $helper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_scopeConfig = $this->getMock(
            'Magento\Framework\App\Config\ScopeConfigInterface',
            ['getValue', 'isSetFlag'],
            [],
            '',
            false
        );
        $this->_coreData = $this->getMock('Magento\Core\Helper\Data', ['getDefaultCountry'], [], '', false);
        $this->_model = $helper->getObject(
            'Magento\Paypal\Model\Config',
            ['scopeConfig' => $this->_scopeConfig, 'coreData' => $this->_coreData]
        );
    }

    public function testGetCountryMethods()
    {
        $this->assertNotContains('payflow_direct', $this->_model->getCountryMethods('GB'));
        $this->assertContains(Config::METHOD_WPP_PE_EXPRESS, $this->_model->getCountryMethods('CA'));
        $this->assertNotContains(Config::METHOD_WPP_PE_EXPRESS, $this->_model->getCountryMethods('GB'));
        $this->assertContains(Config::METHOD_WPP_PE_EXPRESS, $this->_model->getCountryMethods('CA'));
        $this->assertContains(Config::METHOD_WPP_EXPRESS, $this->_model->getCountryMethods('DE'));
        $this->assertContains(Config::METHOD_BILLING_AGREEMENT, $this->_model->getCountryMethods('DE'));
    }

    public function testGetBuildNotationCode()
    {
        $this->_model->setMethod('payflow_direct');
        $this->_model->setStoreId(123);
        $this->_scopeConfig->expects($this->once())
            ->method('getValue')
            ->with('paypal/bncode', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, 123)
            ->will($this->returnValue('some BN code'));
        $this->assertEquals('some BN code', $this->_model->getBuildNotationCode());
    }

    public function testIsMethodActive()
    {
        $this->assertFalse($this->_model->isMethodActive('payflow_direct'));
    }

    /**
     * test for eliminating payflow_direct
     */
    public function testIsMethodAvailableWPPPE()
    {
        $this->assertFalse($this->_model->isMethodAvailable('payflow_direct'));
    }

    /**
     * @dataProvider isMethodAvailableDataProvider
     */
    public function testIsMethodAvailableForIsMethodActive($methodName, $expected)
    {
        $this->_scopeConfig->expects($this->any())
            ->method('getValue')
            ->with('paypal/general/merchant_country')
            ->will($this->returnValue('US'));
        $this->_scopeConfig->expects($this->exactly(2))
            ->method('isSetFlag')
            ->withAnyParameters()
            ->will($this->returnValue(true));

        $this->_model->setMethod($methodName);
        $this->assertEquals($expected, $this->_model->isMethodAvailable($methodName));
    }

    /**
     * @return array
     */
    public function isMethodAvailableDataProvider()
    {
        return [
            [Config::METHOD_WPP_EXPRESS, true],
            [Config::METHOD_WPP_BML, true],
            [Config::METHOD_WPP_PE_EXPRESS, true],
            [Config::METHOD_WPP_PE_BML, true],
        ];
    }

    public function testIsCreditCardMethod()
    {
        $this->assertFalse($this->_model->getIsCreditCardMethod('payflow_direct'));
    }

    public function testGetSpecificConfigPath()
    {
        $this->_model->setMethod('payflow_direct');
        $this->assertNull($this->_model->getConfigValue('useccv'));
        $this->assertNull($this->_model->getConfigValue('vendor'));

        // _mapBmlFieldset
        $this->_model->setMethod(Config::METHOD_WPP_BML);
        $this->_scopeConfig->expects($this->once())
            ->method('getValue')
            ->with('payment/' . Config::METHOD_WPP_EXPRESS . '/allow_ba_signup')
            ->will($this->returnValue(1));
        $this->assertEquals(1, $this->_model->getConfigValue('allow_ba_signup'));
    }

    public function testGetSpecificConfigPathPayflow()
    {
        // _mapBmlPayflowFieldset
        $this->_model->setMethod(Config::METHOD_WPP_PE_BML);
        $this->_scopeConfig->expects($this->once())
            ->method('getValue')
            ->with('payment/' . Config::METHOD_WPP_PE_EXPRESS . '/allow_ba_signup')
            ->will($this->returnValue(1));
        $this->assertEquals(1, $this->_model->getConfigValue('allow_ba_signup'));
    }

    /**
     * @dataProvider skipOrderReviewStepDataProvider
     */
    public function testGetPayPalBasicStartUrl($value, $url)
    {
        $this->_scopeConfig->expects($this->once())
            ->method('getValue')
            ->with('payment/paypal_express/skip_order_review_step')
            ->will($this->returnValue($value));
        $this->assertEquals($url, $this->_model->getPayPalBasicStartUrl('token'));
    }

    /**
     * @return array
     */
    public function skipOrderReviewStepDataProvider()
    {
        return [
            [true, 'https://www.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token=token&useraction=commit'],
            [false, 'https://www.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token=token']
        ];
    }

    public function testGetExpressCheckoutOrderUrl()
    {
        $url = 'https://www.paypal.com/cgi-bin/webscr?cmd=_express-checkout&order_id=orderId';
        $this->assertEquals($url, $this->_model->getExpressCheckoutOrderUrl('orderId'));
    }

    public function testGetBmlPublisherId()
    {
        $this->_scopeConfig->expects($this->once())
            ->method('getValue')
            ->with('payment/' . Config::METHOD_WPP_BML . '/publisher_id')
            ->will($this->returnValue('12345'));
        $this->assertEquals('12345', $this->_model->getBmlPublisherId());
    }

    /**
     * @dataProvider getBmlPositionDataProvider
     */
    public function testGetBmlPosition($section, $expected)
    {
        $this->_scopeConfig->expects($this->once())
            ->method('getValue')
            ->with('payment/' . Config::METHOD_WPP_BML . '/' . $section . '_position')
            ->will($this->returnValue($expected));
        $this->assertEquals($expected, $this->_model->getBmlPosition($section));
    }

    /**
     * @return array
     */
    public function getBmlPositionDataProvider()
    {
        return [
            ['head', 'left'],
            ['checkout', 'top']
        ];
    }

    /**
     * @dataProvider getBmlSizeDataProvider
     */
    public function testGetBmlSize($section, $expected)
    {
        $this->_scopeConfig->expects($this->once())
            ->method('getValue')
            ->with('payment/' . Config::METHOD_WPP_BML . '/' . $section . '_size')
            ->will($this->returnValue($expected));
        $this->assertEquals($expected, $this->_model->getBmlSize($section));
    }

    /**
     * @return array
     */
    public function getBmlSizeDataProvider()
    {
        return [
            ['head', '125x75'],
            ['checkout', ['50x50']]
        ];
    }

    /**
     * @dataProvider dataProviderGetBmlDisplay
     */
    public function testGetBmlDisplay($section, $expectedValue, $expectedFlag, $expected)
    {
        $this->_model->setStoreId(1);
        $this->_coreData->expects($this->any())
            ->method('getDefaultCountry')
            ->with(1)
            ->will($this->returnValue('US'));
        $this->_scopeConfig->expects($this->any())
            ->method('isSetFlag')
            ->will($this->returnValue($expectedFlag));
        $this->_scopeConfig->expects($this->any())
            ->method('getValue')
            ->will($this->returnValueMap([
                ['payment/' . Config::METHOD_WPP_BML . '/' . $section . '_display', 'store', 1, $expectedValue],
                ['payment/' . Config::METHOD_WPP_BML . '/active', 'store', 1, $expectedValue],
                ['payment/' . Config::METHOD_WPP_PE_BML . '/active', 'store', 1, $expectedValue],
            ]));
        $this->assertEquals($expected, $this->_model->getBmlDisplay($section));
    }

    /**
     * @return array
     */
    public function dataProviderGetBmlDisplay()
    {
        return [
            ['head', true, true, true],
            ['head', true, false, false],
            ['head', false, true, false],
            ['head', false, false, false],
        ];
    }
}

<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Test\Unit\Model;

use Magento\Paypal\Model\Config;
use Magento\Store\Model\ScopeInterface;

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
     * @var \Magento\Directory\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $directoryHelper;

    protected function setUp()
    {
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_scopeConfig = $this->getMock(
            'Magento\Framework\App\Config\ScopeConfigInterface',
            ['getValue', 'isSetFlag'],
            [],
            '',
            false
        );
        $this->directoryHelper = $this->getMock('Magento\Directory\Helper\Data', ['getDefaultCountry'], [], '', false);
        $this->_model = $helper->getObject(
            'Magento\Paypal\Model\Config',
            ['scopeConfig' => $this->_scopeConfig, 'directoryHelper' => $this->directoryHelper]
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
        $this->assertNotContains(Config::METHOD_WPP_PE_BML, $this->_model->getCountryMethods('other'));
        $this->assertNotContains(Config::METHOD_WPP_BML, $this->_model->getCountryMethods('other'));
        $this->assertNotContains(Config::METHOD_WPP_PE_BML, $this->_model->getCountryMethods('CA'));
        $this->assertNotContains(Config::METHOD_WPP_BML, $this->_model->getCountryMethods('CA'));
        $this->assertNotContains(Config::METHOD_WPP_PE_BML, $this->_model->getCountryMethods('GB'));
        $this->assertNotContains(Config::METHOD_WPP_BML, $this->_model->getCountryMethods('GB'));
        $this->assertNotContains(Config::METHOD_WPP_PE_BML, $this->_model->getCountryMethods('AU'));
        $this->assertNotContains(Config::METHOD_WPP_BML, $this->_model->getCountryMethods('AU'));
        $this->assertNotContains(Config::METHOD_WPP_PE_BML, $this->_model->getCountryMethods('NZ'));
        $this->assertNotContains(Config::METHOD_WPP_BML, $this->_model->getCountryMethods('NZ'));
        $this->assertNotContains(Config::METHOD_WPP_PE_BML, $this->_model->getCountryMethods('JP'));
        $this->assertNotContains(Config::METHOD_WPP_BML, $this->_model->getCountryMethods('JP'));
        $this->assertNotContains(Config::METHOD_WPP_PE_BML, $this->_model->getCountryMethods('FR'));
        $this->assertNotContains(Config::METHOD_WPP_BML, $this->_model->getCountryMethods('FR'));
        $this->assertNotContains(Config::METHOD_WPP_PE_BML, $this->_model->getCountryMethods('IT'));
        $this->assertNotContains(Config::METHOD_WPP_BML, $this->_model->getCountryMethods('IT'));
        $this->assertNotContains(Config::METHOD_WPP_PE_BML, $this->_model->getCountryMethods('ES'));
        $this->assertNotContains(Config::METHOD_WPP_BML, $this->_model->getCountryMethods('ES'));
        $this->assertNotContains(Config::METHOD_WPP_PE_BML, $this->_model->getCountryMethods('HK'));
        $this->assertNotContains(Config::METHOD_WPP_BML, $this->_model->getCountryMethods('HK'));
        $this->assertNotContains(Config::METHOD_WPP_PE_BML, $this->_model->getCountryMethods('DE'));
        $this->assertNotContains(Config::METHOD_WPP_BML, $this->_model->getCountryMethods('DE'));
    }

    public function testGetBuildNotationCode()
    {
        $this->_model->setMethod('payflow_direct');
        $this->_model->setStoreId(123);
        $this->_scopeConfig->expects($this->once())
            ->method('getValue')
            ->with('paypal/bncode', ScopeInterface::SCOPE_STORE, 123)
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
        $this->assertNull($this->_model->getValue('useccv'));
        $this->assertNull($this->_model->getValue('vendor'));

        // _mapBmlFieldset
        $this->_model->setMethod(Config::METHOD_WPP_BML);
        $this->_scopeConfig->expects($this->once())
            ->method('getValue')
            ->with('payment/' . Config::METHOD_WPP_EXPRESS . '/allow_ba_signup')
            ->will($this->returnValue(1));
        $this->assertEquals(1, $this->_model->getValue('allow_ba_signup'));
    }

    public function testGetSpecificConfigPathPayflow()
    {
        // _mapBmlPayflowFieldset
        $this->_model->setMethod(Config::METHOD_WPP_PE_BML);
        $this->_scopeConfig->expects($this->once())
            ->method('getValue')
            ->with('payment/' . Config::METHOD_WPP_PE_EXPRESS . '/allow_ba_signup')
            ->will($this->returnValue(1));
        $this->assertEquals(1, $this->_model->getValue('allow_ba_signup'));
    }

    public function testGetSpecificConfigPathPayflowAdvancedLink()
    {
        // _mapWpukFieldset
        $this->_model->setMethod(Config::METHOD_PAYFLOWADVANCED);
        $this->_scopeConfig->expects($this->once())
            ->method('getValue')
            ->with('payment/' . Config::METHOD_PAYFLOWADVANCED . '/payment_action')
            ->willReturn('Authorization');
        $this->assertEquals('Authorization', $this->_model->getValue('payment_action'));
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
        $this->directoryHelper->expects($this->any())
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

    /**
     * @param string $localeCode
     * @param float|null $orderTotal
     * @param string|null $pal
     * @param string $areButtonDynamic
     * @param bool $sandboxFlag
     * @param string $buttonType
     * @param string $result
     * @dataProvider dataProviderGetExpressCheckoutShortcutImageUrl
     */
    public function testGetExpressCheckoutShortcutImageUrl(
        $localeCode,
        $orderTotal,
        $pal,
        $areButtonDynamic,
        $sandboxFlag,
        $buttonType,
        $result
    ) {
        $this->_model->setMethod(Config::METHOD_WPP_EXPRESS);
        $this->_model->setStoreId(123);

        $this->_scopeConfig->expects($this->any())
            ->method('getValue')
            ->willReturnMap([
                ['paypal/wpp/button_flavor', ScopeInterface::SCOPE_STORE, 123, $areButtonDynamic],
                ['paypal/wpp/sandbox_flag', ScopeInterface::SCOPE_STORE, 123, $sandboxFlag],
                ['paypal/wpp/button_type', ScopeInterface::SCOPE_STORE, 123, $buttonType],
            ]);

        $this->assertEquals(
            $result,
            $this->_model->getExpressCheckoutShortcutImageUrl($localeCode, $orderTotal, $pal)
        );
    }

    /**
     * @return array
     */
    public function dataProviderGetExpressCheckoutShortcutImageUrl()
    {
        return [
            [
                'en_US', null, null, Config::EC_FLAVOR_DYNAMIC, true, Config::EC_BUTTON_TYPE_SHORTCUT,
                'https://www.paypalobjects.com/webstatic/en_US/i/buttons/checkout-logo-medium.png'
            ],
            [
                'en_GB', null, null, Config::EC_FLAVOR_DYNAMIC, true, Config::EC_BUTTON_TYPE_SHORTCUT,
                'https://fpdbs.sandbox.paypal.com/dynamicimageweb?cmd=_dynamic-image&buttontype=ecshortcut&locale=en_GB'
            ],
            [
                'en_GB', null, null, Config::EC_FLAVOR_DYNAMIC, false, Config::EC_BUTTON_TYPE_SHORTCUT,
                'https://fpdbs.paypal.com/dynamicimageweb?cmd=_dynamic-image&buttontype=ecshortcut&locale=en_GB'
            ],
            [
                'en_US', null, null, Config::EC_FLAVOR_STATIC, false, Config::EC_BUTTON_TYPE_MARK,
                'https://www.paypalobjects.com/webstatic/en_US/i/buttons/pp-acceptance-medium.png'
            ],
            [
                'en_US', null, null, Config::EC_FLAVOR_STATIC, true, Config::EC_BUTTON_TYPE_SHORTCUT,
                'https://www.paypalobjects.com/webstatic/en_US/i/buttons/checkout-logo-medium.png'],
            [
                'en_GB', null, null, Config::EC_FLAVOR_STATIC, true, Config::EC_BUTTON_TYPE_SHORTCUT,
                'https://www.paypal.com/en_GB/i/btn/btn_xpressCheckout.gif'
            ],
        ];
    }

    /**
     * @param string $localeCode
     * @param float|null $orderTotal
     * @param string|null $pal
     * @param string|null $staticSize
     * @param string $areButtonDynamic
     * @param bool $sandboxFlag
     * @param string $result
     * @dataProvider dataProviderGetPaymentMarkImageUrl
     */
    public function testGetPaymentMarkImageUrl(
        $localeCode,
        $orderTotal,
        $pal,
        $staticSize,
        $areButtonDynamic,
        $sandboxFlag,
        $result
    ) {
        $this->_model->setMethod(Config::METHOD_WPP_EXPRESS);
        $this->_model->setStoreId(123);

        $this->_scopeConfig->expects($this->any())
            ->method('getValue')
            ->willReturnMap([
                ['paypal/wpp/button_flavor', ScopeInterface::SCOPE_STORE, 123, $areButtonDynamic],
                ['paypal/wpp/sandbox_flag', ScopeInterface::SCOPE_STORE, 123, $sandboxFlag],
            ]);

        $this->assertEquals(
            $result,
            $this->_model->getPaymentMarkImageUrl($localeCode, $orderTotal, $pal, $staticSize)
        );
    }

    /**
     * @return array
     */
    public function dataProviderGetPaymentMarkImageUrl()
    {
        return [
            [
                'en_US', null, null, 'small', Config::EC_FLAVOR_DYNAMIC, true,
                'https://www.paypalobjects.com/webstatic/en_US/i/buttons/ppcredit-logo-medium.png'
            ],
            [
                'en_GB', null, null, 'small', Config::EC_FLAVOR_DYNAMIC, true,
                'https://fpdbs.sandbox.paypal.com/dynamicimageweb?cmd=_dynamic-image&buttontype=ecmark&locale=en_GB'
            ],
            [
                'en_GB', null, null, 'small', Config::EC_FLAVOR_DYNAMIC, false,
                'https://fpdbs.paypal.com/dynamicimageweb?cmd=_dynamic-image&buttontype=ecmark&locale=en_GB'
            ],
            [
                'en_US', null, null, 'medium', Config::EC_FLAVOR_STATIC, true,
                'https://www.paypalobjects.com/webstatic/en_US/i/buttons/pp-acceptance-medium.png'
            ],
            [
                'en_US', null, null, 'medium', Config::EC_FLAVOR_STATIC, true,
                'https://www.paypalobjects.com/webstatic/en_US/i/buttons/pp-acceptance-medium.png'
            ],
            [
                'en_US', null, null, 'large', Config::EC_FLAVOR_STATIC, true,
                'https://www.paypalobjects.com/webstatic/en_US/i/buttons/pp-acceptance-large.png'
            ],
            [
                'en_GB', null, null, 'affected', Config::EC_FLAVOR_STATIC, true,
                'https://www.paypalobjects.com/webstatic/en_US/i/buttons/pp-acceptance-medium.png'
            ],
        ];
    }
}

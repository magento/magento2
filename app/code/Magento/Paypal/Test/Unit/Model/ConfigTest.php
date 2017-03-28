<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
    private $model;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeConfig;

    /**
     * @var \Magento\Directory\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    private $directoryHelper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManager;

    /**
     * @var \Magento\Payment\Model\Source\CctypeFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $ccTypeFactory;

    /**
     * @var \Magento\Paypal\Model\CertFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $certFactory;

    protected function setUp()
    {
        $this->scopeConfig = $this->getMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);

        $this->directoryHelper = $this->getMockBuilder(\Magento\Directory\Helper\Data::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->storeManager = $this->getMock(\Magento\Store\Model\StoreManagerInterface::class);

        $this->ccTypeFactory = $this->getMockBuilder(\Magento\Payment\Model\Source\CctypeFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->certFactory = $this->getMockBuilder(\Magento\Paypal\Model\CertFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = new Config(
            $this->scopeConfig,
            $this->directoryHelper,
            $this->storeManager,
            $this->ccTypeFactory,
            $this->certFactory
        );
    }

    public function testGetCountryMethods()
    {
        $this->assertNotContains('payflow_direct', $this->model->getCountryMethods('GB'));
        $this->assertContains(Config::METHOD_WPP_PE_EXPRESS, $this->model->getCountryMethods('CA'));
        $this->assertNotContains(Config::METHOD_WPP_PE_EXPRESS, $this->model->getCountryMethods('GB'));
        $this->assertContains(Config::METHOD_WPP_PE_EXPRESS, $this->model->getCountryMethods('CA'));
        $this->assertContains(Config::METHOD_WPP_EXPRESS, $this->model->getCountryMethods('DE'));
        $this->assertContains(Config::METHOD_BILLING_AGREEMENT, $this->model->getCountryMethods('DE'));
        $this->assertNotContains(Config::METHOD_WPP_PE_BML, $this->model->getCountryMethods('other'));
        $this->assertNotContains(Config::METHOD_WPP_BML, $this->model->getCountryMethods('other'));
        $this->assertNotContains(Config::METHOD_WPP_PE_BML, $this->model->getCountryMethods('CA'));
        $this->assertNotContains(Config::METHOD_WPP_BML, $this->model->getCountryMethods('CA'));
        $this->assertNotContains(Config::METHOD_WPP_PE_BML, $this->model->getCountryMethods('GB'));
        $this->assertNotContains(Config::METHOD_WPP_BML, $this->model->getCountryMethods('GB'));
        $this->assertNotContains(Config::METHOD_WPP_PE_BML, $this->model->getCountryMethods('AU'));
        $this->assertNotContains(Config::METHOD_WPP_BML, $this->model->getCountryMethods('AU'));
        $this->assertNotContains(Config::METHOD_WPP_PE_BML, $this->model->getCountryMethods('NZ'));
        $this->assertNotContains(Config::METHOD_WPP_BML, $this->model->getCountryMethods('NZ'));
        $this->assertNotContains(Config::METHOD_WPP_PE_BML, $this->model->getCountryMethods('JP'));
        $this->assertNotContains(Config::METHOD_WPP_BML, $this->model->getCountryMethods('JP'));
        $this->assertNotContains(Config::METHOD_WPP_PE_BML, $this->model->getCountryMethods('FR'));
        $this->assertNotContains(Config::METHOD_WPP_BML, $this->model->getCountryMethods('FR'));
        $this->assertNotContains(Config::METHOD_WPP_PE_BML, $this->model->getCountryMethods('IT'));
        $this->assertNotContains(Config::METHOD_WPP_BML, $this->model->getCountryMethods('IT'));
        $this->assertNotContains(Config::METHOD_WPP_PE_BML, $this->model->getCountryMethods('ES'));
        $this->assertNotContains(Config::METHOD_WPP_BML, $this->model->getCountryMethods('ES'));
        $this->assertNotContains(Config::METHOD_WPP_PE_BML, $this->model->getCountryMethods('HK'));
        $this->assertNotContains(Config::METHOD_WPP_BML, $this->model->getCountryMethods('HK'));
        $this->assertNotContains(Config::METHOD_WPP_PE_BML, $this->model->getCountryMethods('DE'));
        $this->assertNotContains(Config::METHOD_WPP_BML, $this->model->getCountryMethods('DE'));
    }

    public function testIsMethodActive()
    {
        $this->assertFalse($this->model->isMethodActive('payflow_direct'));
    }

    /**
     * test for eliminating payflow_direct
     */
    public function testIsMethodAvailableWPPPE()
    {
        $this->assertFalse($this->model->isMethodAvailable('payflow_direct'));
    }

    /**
     * @dataProvider isMethodAvailableDataProvider
     */
    public function testIsMethodAvailableForIsMethodActive($methodName, $expected)
    {
        $this->scopeConfig->expects($this->any())
            ->method('getValue')
            ->with('paypal/general/merchant_country')
            ->will($this->returnValue('US'));
        $this->scopeConfig->expects($this->exactly(2))
            ->method('isSetFlag')
            ->withAnyParameters()
            ->will($this->returnValue(true));

        $this->model->setMethod($methodName);
        $this->assertEquals($expected, $this->model->isMethodAvailable($methodName));
    }

    public function testGetMerchantCountryPaypal()
    {
        $this->scopeConfig->expects(static::once())
            ->method('getValue')
            ->with(
                'paypal/general/merchant_country',
                ScopeInterface::SCOPE_STORE,
                null
            )->willReturn('US');

        $this->directoryHelper->expects(static::never())
            ->method('getDefaultCountry');

        static::assertEquals('US', $this->model->getMerchantCountry());
    }

    public function testGetMerchantCountryGeneral()
    {
        $this->scopeConfig->expects(static::once())
            ->method('getValue')
            ->with(
                'paypal/general/merchant_country',
                ScopeInterface::SCOPE_STORE,
                null
            )->willReturn(null);

        $this->directoryHelper->expects(static::once())
            ->method('getDefaultCountry')
            ->with(null)
            ->willReturn('US');

        static::assertEquals('US', $this->model->getMerchantCountry());
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
        $this->assertFalse($this->model->getIsCreditCardMethod('payflow_direct'));
    }

    public function testGetSpecificConfigPath()
    {
        $this->model->setMethod('payflow_direct');
        $this->assertNull($this->model->getValue('useccv'));
        $this->assertNull($this->model->getValue('vendor'));

        // _mapBmlFieldset
        $this->model->setMethod(Config::METHOD_WPP_BML);
        $this->scopeConfig->expects($this->once())
            ->method('getValue')
            ->with('payment/' . Config::METHOD_WPP_EXPRESS . '/allow_ba_signup')
            ->will($this->returnValue(1));
        $this->assertEquals(1, $this->model->getValue('allow_ba_signup'));
    }

    public function testGetSpecificConfigPathPayflow()
    {
        // _mapBmlPayflowFieldset
        $this->model->setMethod(Config::METHOD_WPP_PE_BML);
        $this->scopeConfig->expects($this->once())
            ->method('getValue')
            ->with('payment/' . Config::METHOD_WPP_PE_EXPRESS . '/allow_ba_signup')
            ->will($this->returnValue(1));
        $this->assertEquals(1, $this->model->getValue('allow_ba_signup'));
    }

    public function testGetSpecificConfigPathPayflowAdvancedLink()
    {
        // _mapWpukFieldset
        $this->model->setMethod(Config::METHOD_PAYFLOWADVANCED);
        $this->scopeConfig->expects($this->once())
            ->method('getValue')
            ->with('payment/' . Config::METHOD_PAYFLOWADVANCED . '/payment_action')
            ->willReturn('Authorization');
        $this->assertEquals('Authorization', $this->model->getValue('payment_action'));
    }

    /**
     * @dataProvider skipOrderReviewStepDataProvider
     */
    public function testGetPayPalBasicStartUrl($value, $url)
    {
        $this->scopeConfig->expects($this->once())
            ->method('getValue')
            ->with('payment/paypal_express/skip_order_review_step')
            ->will($this->returnValue($value));
        $this->assertEquals($url, $this->model->getPayPalBasicStartUrl('token'));
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
        $this->assertEquals($url, $this->model->getExpressCheckoutOrderUrl('orderId'));
    }

    public function testGetBmlPublisherId()
    {
        $this->scopeConfig->expects($this->once())
            ->method('getValue')
            ->with('payment/' . Config::METHOD_WPP_BML . '/publisher_id')
            ->will($this->returnValue('12345'));
        $this->assertEquals('12345', $this->model->getBmlPublisherId());
    }

    /**
     * @dataProvider getBmlPositionDataProvider
     */
    public function testGetBmlPosition($section, $expected)
    {
        $this->scopeConfig->expects($this->once())
            ->method('getValue')
            ->with('payment/' . Config::METHOD_WPP_BML . '/' . $section . '_position')
            ->will($this->returnValue($expected));
        $this->assertEquals($expected, $this->model->getBmlPosition($section));
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
        $this->scopeConfig->expects($this->once())
            ->method('getValue')
            ->with('payment/' . Config::METHOD_WPP_BML . '/' . $section . '_size')
            ->will($this->returnValue($expected));
        $this->assertEquals($expected, $this->model->getBmlSize($section));
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
        $this->model->setStoreId(1);
        $this->directoryHelper->expects($this->any())
            ->method('getDefaultCountry')
            ->with(1)
            ->will($this->returnValue('US'));
        $this->scopeConfig->expects($this->any())
            ->method('isSetFlag')
            ->will($this->returnValue($expectedFlag));
        $this->scopeConfig->expects($this->any())
            ->method('getValue')
            ->will($this->returnValueMap([
                ['payment/' . Config::METHOD_WPP_BML . '/' . $section . '_display', 'store', 1, $expectedValue],
                ['payment/' . Config::METHOD_WPP_BML . '/active', 'store', 1, $expectedValue],
                ['payment/' . Config::METHOD_WPP_PE_BML . '/active', 'store', 1, $expectedValue],
            ]));
        $this->assertEquals($expected, $this->model->getBmlDisplay($section));
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
        $this->model->setMethod(Config::METHOD_WPP_EXPRESS);
        $this->model->setStoreId(123);

        $this->scopeConfig->expects($this->any())
            ->method('getValue')
            ->willReturnMap([
                ['paypal/wpp/button_flavor', ScopeInterface::SCOPE_STORE, 123, $areButtonDynamic],
                ['paypal/wpp/sandbox_flag', ScopeInterface::SCOPE_STORE, 123, $sandboxFlag],
                ['paypal/wpp/button_type', ScopeInterface::SCOPE_STORE, 123, $buttonType],
            ]);

        $this->assertEquals(
            $result,
            $this->model->getExpressCheckoutShortcutImageUrl($localeCode, $orderTotal, $pal)
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
        $this->model->setMethod(Config::METHOD_WPP_EXPRESS);
        $this->model->setStoreId(123);

        $this->scopeConfig->expects($this->any())
            ->method('getValue')
            ->willReturnMap([
                ['paypal/wpp/button_flavor', ScopeInterface::SCOPE_STORE, 123, $areButtonDynamic],
                ['paypal/wpp/sandbox_flag', ScopeInterface::SCOPE_STORE, 123, $sandboxFlag],
            ]);

        $this->assertEquals(
            $result,
            $this->model->getPaymentMarkImageUrl($localeCode, $orderTotal, $pal, $staticSize)
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

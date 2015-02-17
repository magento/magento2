<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Core\Helper;

use Magento\Framework\Pricing\PriceCurrencyInterface;

class DataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $priceCurrencyMock;

    public function setUp()
    {
        $this->priceCurrencyMock = $this->getMock('Magento\Framework\Pricing\PriceCurrencyInterface');
        $this->objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
    }

    /**
     * @param string $amount
     * @param bool $format
     * @param bool $includeContainer
     * @param string $result
     * @dataProvider currencyDataProvider
     */
    public function testCurrency($amount, $format, $includeContainer, $result)
    {
        if ($format) {
            $this->priceCurrencyMock->expects($this->once())
                ->method('convertAndFormat')
                ->with($amount, $includeContainer)
                ->will($this->returnValue($result));
        } else {
            $this->priceCurrencyMock->expects($this->once())
                ->method('convert')
                ->with($amount)
                ->will($this->returnValue($result));
        }
        $helper = $this->getHelper(['priceCurrency' => $this->priceCurrencyMock]);
        $this->assertEquals($result, $helper->currency($amount, $format, $includeContainer));
    }

    public function currencyDataProvider()
    {
        return [
            ['amount' => '100', 'format' => true, 'includeContainer' => true, 'result' => '100grn.'],
            ['amount' => '115', 'format' => true, 'includeContainer' => false, 'result' => '1150'],
            ['amount' => '120', 'format' => false, 'includeContainer' => null, 'result' => '1200'],
        ];
    }

    /**
     * @param string $amount
     * @param string $store
     * @param bool $format
     * @param bool $includeContainer
     * @param string $result
     * @dataProvider currencyByStoreDataProvider
     */
    public function testCurrencyByStore($amount, $store, $format, $includeContainer, $result)
    {
        if ($format) {
            $this->priceCurrencyMock->expects($this->once())
                ->method('convertAndFormat')
                ->with($amount, $includeContainer, PriceCurrencyInterface::DEFAULT_PRECISION, $store)
                ->will($this->returnValue($result));
        } else {
            $this->priceCurrencyMock->expects($this->once())
                ->method('convert')
                ->with($amount, $store)
                ->will($this->returnValue($result));
        }
        $helper = $this->getHelper(['priceCurrency' => $this->priceCurrencyMock]);
        $this->assertEquals($result, $helper->currencyByStore($amount, $store, $format, $includeContainer));
    }

    public function currencyByStoreDataProvider()
    {
        return [
            ['amount' => '10', 'store' => 1, 'format' => true, 'includeContainer' => true, 'result' => '10grn.'],
            ['amount' => '115', 'store' => 4,  'format' => true, 'includeContainer' => false, 'result' => '1150'],
            ['amount' => '120', 'store' => 5,  'format' => false, 'includeContainer' => null, 'result' => '1200'],
        ];
    }

    public function testFormatCurrency()
    {
        $amount = '120';
        $includeContainer = false;
        $result = '10grn.';

        $this->priceCurrencyMock->expects($this->once())
            ->method('convertAndFormat')
            ->with($amount, $includeContainer)
            ->will($this->returnValue($result));

        $helper = $this->getHelper(['priceCurrency' => $this->priceCurrencyMock]);
        $this->assertEquals($result, $helper->formatCurrency($amount, $includeContainer));
    }

    public function testFormatPrice()
    {
        $amount = '120';
        $includeContainer = false;
        $result = '10grn.';

        $this->priceCurrencyMock->expects($this->once())
            ->method('format')
            ->with($amount, $includeContainer)
            ->will($this->returnValue($result));

        $helper = $this->getHelper(['priceCurrency' => $this->priceCurrencyMock]);
        $this->assertEquals($result, $helper->formatPrice($amount, $includeContainer));
    }

    /**
     * @param array $allowedIps
     * @param bool $expected
     * @dataProvider isDevAllowedDataProvider
     */
    public function testIsDevAllowed($allowedIps, $expected, $callNum = 1)
    {
        $storeId = 'storeId';

        $scopeConfigMock = $this->getMockBuilder('Magento\Framework\App\Config\ScopeConfigInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(
                Data::XML_PATH_DEV_ALLOW_IPS,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $storeId
            )->will($this->returnValue($allowedIps));

        $remoteAddressMock = $this->getMockBuilder('Magento\Framework\HTTP\PhpEnvironment\RemoteAddress')
            ->disableOriginalConstructor()
            ->getMock();
        $remoteAddressMock->expects($this->once())
            ->method('getRemoteAddress')
            ->will($this->returnValue('remoteAddress'));

        $httpHeaderMock = $this->getMockBuilder('Magento\Framework\HTTP\Header')
            ->disableOriginalConstructor()
            ->getMock();
        $httpHeaderMock->expects($this->exactly($callNum))
            ->method('getHttpHost')
            ->will($this->returnValue('httpHost'));

        $context = $this->objectManager->getObject(
            'Magento\Framework\App\Helper\Context',
            [
                'remoteAddress' => $remoteAddressMock,
                'httpHeader' => $httpHeaderMock,
            ]
        );
        $helper = $this->getHelper(
            [
                'scopeConfig' => $scopeConfigMock,
                'context' => $context,
            ]
        );
        $this->assertEquals($expected, $helper->isDevAllowed($storeId));
    }

    public function isDevAllowedDataProvider()
    {
        return [
            'allow_nothing' => [
                '',
                true,
                0,
            ],
            'allow_remote_address' => [
                'ip1, ip2, remoteAddress',
                true,
                0,
            ],
            'allow_http_host' => [
                'ip1, ip2, httpHost',
                true,
            ],
            'allow_neither' => [
                'ip1, ip2, ip3',
                false,
            ],
        ];
    }

    public function testGetCacheTypes()
    {
        $cachedTypes = [
            'type1' => ['label' => 'node1', 'other' => 'other1'],
            'type2' => ['label' => 'node2', 'other' => 'other2'],
            'type3' => ['other' => 'other3'],
        ];
        $types = [
            'type1' => 'node1',
            'type2' => 'node2',
        ];
        $cacheConfigMock = $this->getMockBuilder('Magento\Framework\Cache\ConfigInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $cacheConfigMock->expects($this->once())
            ->method('getTypes')
            ->will($this->returnValue($cachedTypes));
        $context = $this->objectManager->getObject(
            'Magento\Framework\App\Helper\Context',
            [
                'cacheConfig' => $cacheConfigMock,
            ]
        );
        $helper = $this->getHelper(
            [
                'context' => $context,
            ]
        );

        $this->assertEquals($types, $helper->getCacheTypes());
    }

    public function testJsonEncode()
    {
        $valueToEncode = 'valueToEncode';
        $translateInlineMock = $this->getMockBuilder('Magento\Framework\Translate\InlineInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $translateInlineMock->expects($this->once())
            ->method('processResponseBody');
        $context = $this->objectManager->getObject(
            'Magento\Framework\App\Helper\Context',
            [
                'translateInline' => $translateInlineMock,
            ]
        );
        $helper = $this->getHelper(
            [
                'context' => $context,
            ]
        );

        $this->assertEquals('"valueToEncode"', $helper->jsonEncode($valueToEncode));
    }

    public function testJsonDecode()
    {
        $helper = $this->getHelper([]);
        $this->assertEquals('"valueToDecode"', $helper->jsonEncode('valueToDecode'));
    }

    public function testGetDefaultCountry()
    {
        $storeId = 'storeId';
        $country = 'country';

        $scopeConfigMock = $this->getMockBuilder('Magento\Framework\App\Config\ScopeConfigInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(
                Data::XML_PATH_DEFAULT_COUNTRY,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $storeId
            )->will($this->returnValue($country));

        $helper = $this->getHelper(
            [
                'scopeConfig' => $scopeConfigMock,
            ]
        );
        $this->assertEquals($country, $helper->getDefaultCountry($storeId));
    }

    /**
     * @param bool $expected
     * @param bool $dbCompatibleMode
     * @dataProvider useDbCompatibleModelDataProvider
     */
    public function testUseDbCompatibleModel($expected, $dbCompatibleMode = null)
    {
        $arguments = [];
        if (null !== $dbCompatibleMode) {
            $arguments['dbCompatibleMode'] = $dbCompatibleMode;
        }
        $helper = $this->getHelper($arguments);
        $this->assertEquals($expected, $helper->useDbCompatibleMode());
    }

    public function useDbCompatibleModelDataProvider()
    {
        return [
            'default' => [true],
            'false' => [false, false],
            'true' => [true, true],
        ];
    }

    /**
     * Get helper instance
     *
     * @param array $arguments
     * @return Data
     */
    private function getHelper($arguments)
    {
        return $this->objectManager->getObject('Magento\Core\Helper\Data', $arguments);
    }
}

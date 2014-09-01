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
namespace Magento\Store\Model;

/**
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @SuppressWarnings(PHPMD.UnusedLocalVariable)
 */
class StoreTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $objectManagerHelper;

    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $requestMock;

    public function setUp()
    {
        $this->objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->requestMock = $this->getMock('\Magento\Framework\App\RequestInterface', [
            'getRequestString',
            'getModuleName',
            'setModuleName',
            'getActionName',
            'setActionName',
            'getParam',
            'getQuery',
            'getCookie',
            'getDistroBaseUrl',
        ], [], '', false);
    }

    /**
     * @dataProvider loadDataProvider
     *
     * @param string|int $key
     * @param string $field
     */
    public function testLoad($key, $field)
    {
        /** @var \Magento\Store\Model\Resource\Store $resource */
        $resource = $this->getMock(
            '\Magento\Store\Model\Resource\Store',
            array('load', 'getIdFieldName', '__wakeup'),
            [],
            '',
            false
        );
        $resource->expects($this->atLeastOnce())->method('load')
            ->with($this->isInstanceOf('\Magento\Store\Model\Store'), $this->equalTo($key), $this->equalTo($field))
            ->will($this->returnSelf());
        $resource->expects($this->atLeastOnce())->method('getIdFieldName')->will($this->returnValue('store_id'));
        /** @var \Magento\Store\Model\Store $model */
        $model = $this->objectManagerHelper->getObject('Magento\Store\Model\Store', array('resource' => $resource));
        $model->load($key);
    }

    public function loadDataProvider()
    {
        return array(
            array(1, null),
            array('default', 'code'),
        );
    }

    public function testSetWebsite()
    {
        $website = $this->getMock('\Magento\Store\Model\Website', array('getId', '__wakeup'), [], '', false);
        $website->expects($this->atLeastOnce())->method('getId')->will($this->returnValue(2));
        /** @var \Magento\Store\Model\Store $model */
        $model = $this->objectManagerHelper->getObject('Magento\Store\Model\Store');
        $model->setWebsite($website);
        $this->assertEquals(2, $model->getWebsiteId());
    }

    /**
     * @dataProvider getWebsiteDataProvider
     *
     * @param int $websiteId
     * @param \Magento\Store\Model\Website $website
     */
    public function testGetWebsite($websiteId, $website)
    {
        $storeManager = $this->getMockForAbstractClass('\Magento\Store\Model\StoreManagerInterface');
        $storeManager->expects($this->any())
            ->method('getWebsite')
            ->with($websiteId)
            ->will($this->returnValue($website));
        /** @var \Magento\Store\Model\Store $model */
        $model = $this->objectManagerHelper->getObject(
            'Magento\Store\Model\Store',
            array('storeManager' => $storeManager)
        );
        $model->setWebsiteId($websiteId);
        $this->assertEquals($website, $model->getWebsite());
    }

    public function getWebsiteDataProvider()
    {
        $website = $this->getMock('\Magento\Store\Model\Website', array('__wakeup'), [], '', false);
        return array(
            array(null, false),
            array(2, $website)
        );
    }

    public function testGetUrl()
    {
        $params = array('_scope_to_url' => true);
        $defaultStore = $this->getMock('\Magento\Store\Model\Store', array('getId', '__wakeup'), [], '', false);
        $defaultStore->expects($this->atLeastOnce())->method('getId')->will($this->returnValue(5));


        $url = $this->getMockForAbstractClass('\Magento\Framework\UrlInterface');
        $url->expects($this->atLeastOnce())->method('setScope')->will($this->returnSelf());
        $url->expects($this->atLeastOnce())->method('getUrl')
            ->with($this->equalTo('test/route'), $this->equalTo($params))
            ->will($this->returnValue('http://test/url'));

        $storeManager = $this->getMockForAbstractClass('\Magento\Store\Model\StoreManagerInterface');
        $storeManager->expects($this->any())
            ->method('getStore')
            ->will($this->returnValue($defaultStore));

        /** @var \Magento\Store\Model\Store $model */
        $model = $this->objectManagerHelper->getObject(
            'Magento\Store\Model\Store',
            array('storeManager' => $storeManager, 'url' => $url)
        );
        $model->setStoreId(2);
        $this->assertEquals('http://test/url', $model->getUrl('test/route'));
    }

    /**
     * @dataProvider getBaseUrlDataProvider
     *
     * @covers \Magento\Store\Model\Store::getBaseUrl
     * @covers \Magento\Store\Model\Store::getCode
     * @covers \Magento\Store\Model\Store::_updatePathUseRewrites
     * @covers \Magento\Store\Model\Store::_getConfig
     *
     * @param string $type
     * @param boolean $secure
     * @param string $expectedPath
     * @param string $expectedBaseUrl
     */
    public function testGetBaseUrl($type, $secure, $expectedPath, $expectedBaseUrl)
    {
        $this->requestMock->expects($this->any())
            ->method('getDistroBaseUrl')
            ->will($this->returnValue('http://distro.com/'));

        /** @var \Magento\Framework\App\Config\ReinitableConfigInterface $configMock */
        $configMock = $this->getMockForAbstractClass('\Magento\Framework\App\Config\ReinitableConfigInterface');
        $configMock->expects($this->atLeastOnce())
            ->method('getValue')
            ->will($this->returnCallback(
                function ($path, $scope, $scopeCode) use ($secure, $expectedPath) {
                    $url = $secure ? '{{base_url}}' : 'http://domain.com/';
                    return $expectedPath == $path ? $url . $path . '/' : null;
                }
            ));
        /** @var \Magento\Store\Model\Store $model */
        $model = $this->objectManagerHelper->getObject(
            'Magento\Store\Model\Store',
            array(
                'config' => $configMock,
                'request' => $this->requestMock,
                'isCustomEntryPoint' => !$secure,
            )
        );
        $model->setCode('scopeCode');
        $this->assertEquals($expectedBaseUrl, $model->getBaseUrl($type, $secure));
    }

    public function getBaseUrlDataProvider()
    {
        return array(
            array(
                \Magento\Framework\UrlInterface::URL_TYPE_WEB,
                false,
                'web/unsecure/base_url',
                'http://domain.com/web/unsecure/base_url/'
            ),
            array(
                \Magento\Framework\UrlInterface::URL_TYPE_LINK,
                false,
                'web/unsecure/base_link_url',
                'http://domain.com/web/unsecure/base_link_url/index.php/'
            ),
            array(
                \Magento\Framework\UrlInterface::URL_TYPE_DIRECT_LINK,
                false,
                'web/unsecure/base_link_url',
                'http://domain.com/web/unsecure/base_link_url/index.php/'
            ),
            array(
                \Magento\Framework\UrlInterface::URL_TYPE_MEDIA,
                false,
                'web/unsecure/base_media_url',
                'http://domain.com/web/unsecure/base_media_url/'
            ),
            array(
                \Magento\Framework\UrlInterface::URL_TYPE_STATIC,
                false,
                'web/unsecure/base_static_url',
                'http://domain.com/web/unsecure/base_static_url/'
            ),
            array(
                \Magento\Framework\UrlInterface::URL_TYPE_MEDIA,
                false,
                'web/unsecure/base_url',
                'http://domain.com/web/unsecure/base_url/'
            ),
            array(
                \Magento\Framework\UrlInterface::URL_TYPE_STATIC,
                false,
                'web/unsecure/base_url',
                'http://domain.com/web/unsecure/base_url/'
            ),
            array(
                \Magento\Framework\UrlInterface::URL_TYPE_WEB,
                true,
                'web/secure/base_url',
                'http://distro.com/web/secure/base_url/'
            ),
        );
    }

    public function testGetBaseUrlEntryPoint()
    {
        $expectedPath = 'web/unsecure/base_link_url';
        $expectedBaseUrl = 'http://domain.com/web/unsecure/base_link_url/test_script.php/';
        /** @var \Magento\Framework\App\Config\ReinitableConfigInterface $configMock */
        $configMock = $this->getMockForAbstractClass('\Magento\Framework\App\Config\ReinitableConfigInterface');
        $configMock->expects($this->atLeastOnce())
            ->method('getValue')
            ->will($this->returnCallback(
                function ($path, $scope, $scopeCode) use ($expectedPath) {
                    return $expectedPath == $path ? 'http://domain.com/' . $path . '/' : null;
                }
            ));
        /** @var \Magento\Store\Model\Store $model */
        $model = $this->objectManagerHelper->getObject(
            'Magento\Store\Model\Store',
            array(
                'config' => $configMock,
                'isCustomEntryPoint' => false,
            )
        );
        $model->setCode('scopeCode');
        $server = $_SERVER;
        $_SERVER['SCRIPT_FILENAME'] = 'test_script.php';
        $this->assertEquals(
            $expectedBaseUrl,
            $model->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_LINK, false)
        );
        $_SERVER = $server;
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetBaseUrlWrongType()
    {
        /** @var \Magento\Store\Model\Store $model */
        $model = $this->objectManagerHelper->getObject(
            'Magento\Store\Model\Store'
        );
        $model->getBaseUrl('unexpected url type');
    }

    /**
     * @dataProvider getCurrentUrlDataProvider
     *
     * @param boolean $secure
     * @param string $url
     * @param string $expected
     */
    public function testGetCurrentUrl($secure, $url, $expected)
    {
        $defaultStore = $this->getMock('\Magento\Store\Model\Store', array(
            'getId',
            'isCurrentlySecure',
            '__wakeup'
        ), [], '', false);
        $defaultStore->expects($this->atLeastOnce())->method('getId')->will($this->returnValue(5));
        $defaultStore->expects($this->atLeastOnce())->method('isCurrentlySecure')->will($this->returnValue($secure));

        $sidResolver = $this->getMockForAbstractClass('\Magento\Framework\Session\SidResolverInterface');
        $sidResolver->expects($this->any())->method('getSessionIdQueryParam')->will($this->returnValue('SID'));

        $config = $this->getMockForAbstractClass('\Magento\Framework\App\Config\ReinitableConfigInterface');


        $this->requestMock->expects($this->atLeastOnce())->method('getRequestString')->will($this->returnValue(''));
        $this->requestMock->expects($this->atLeastOnce())->method('getQuery')->will($this->returnValue(array(
            'SID' => 'sid'
        )));


        $urlMock = $this->getMockForAbstractClass('\Magento\Framework\UrlInterface');
        $urlMock->expects($this->atLeastOnce())->method('setScope')->will($this->returnSelf());
        $urlMock->expects($this->any())->method('getUrl')
            ->will($this->returnValue($url));

        $storeManager = $this->getMockForAbstractClass('\Magento\Store\Model\StoreManagerInterface');
        $storeManager->expects($this->any())
            ->method('getStore')
            ->will($this->returnValue($defaultStore));

        /** @var \Magento\Store\Model\Store $model */
        $model = $this->objectManagerHelper->getObject(
            'Magento\Store\Model\Store',
            ['storeManager' => $storeManager, 'url' => $urlMock, 'request' => $this->requestMock, 'config' => $config]
        );
        $model->setStoreId(2);
        $model->setCode('scope_code');

        $this->assertEquals($expected, $model->getCurrentUrl(false));
    }

    /**
     * @return array
     */
    public function getCurrentUrlDataProvider()
    {
        return array(
            array(true, 'http://test/url', 'http://test/url?SID=sid&amp;___store=scope_code'),
            array(true, 'http://test/url?SID=sid1&___store=scope', 'http://test/url?SID=sid&amp;___store=scope_code'),
            array(false, 'https://test/url', 'https://test/url?SID=sid&amp;___store=scope_code')
        );
    }

    public function testGetPriceFilter()
    {
        /** @var \Magento\Store\Model\Store $model */
        $model = $this->objectManagerHelper->getObject('Magento\Store\Model\Store');
        $model->setData('base_currency', false);
        $model->setData('current_currency', false);
        $model->setData('default_currency', false);
        $this->assertInstanceOf('\Magento\Framework\Filter\Sprintf', $model->getPriceFilter());

        $filter = $this->getMock('\Magento\Directory\Model\Currency\Filter', [], [], '', false);

        $defaultCurrency = $this->getMock('\Magento\Directory\Model\Currency', [], [], '', false);
        $defaultCurrency->expects($this->any())->method('getFilter')->will($this->returnValue($filter));

        $model = $this->objectManagerHelper->getObject('Magento\Store\Model\Store');
        $model->setData('base_currency', false);
        $model->setData('current_currency', false);
        $model->setData('default_currency', $defaultCurrency);
        $this->assertEquals($filter, $model->getPriceFilter());

        $filter->expects($this->any())->method('setRate')->with(2.1)->will($this->returnSelf());

        $currentCurrency = $this->getMock('\Magento\Directory\Model\Currency', [], [], '', false);
        $currentCurrency->expects($this->any())->method('getFilter')->will($this->returnValue($filter));

        $baseCurrency = $this->getMock('\Magento\Directory\Model\Currency', [], [], '', false);
        $baseCurrency->expects($this->any())->method('getRate')->with($currentCurrency)->will($this->returnValue(2.1));

        $model = $this->objectManagerHelper->getObject('Magento\Store\Model\Store');
        $model->setData('base_currency', $baseCurrency);
        $model->setData('current_currency', $currentCurrency);
        $this->assertEquals($filter, $model->getPriceFilter());
    }

    /**
     * @dataProvider getBaseCurrencyDataProvider
     *
     * @param int $priceScope
     * @param string $currencyCode
     */
    public function testGetBaseCurrency($priceScope, $currencyCode)
    {
        /** @var \Magento\Framework\App\Config\ReinitableConfigInterface $config */
        $config = $this->getMockForAbstractClass('\Magento\Framework\App\Config\ReinitableConfigInterface');
        $config->expects($this->any())
            ->method('getValue')
            ->will($this->returnValueMap(array(
                array('catalog/price/scope', ScopeInterface::SCOPE_STORE, 'scope_code', $priceScope),
                array('currency/options/base', \Magento\Framework\App\ScopeInterface::SCOPE_DEFAULT, null, 'USD'),
                array('currency/options/base', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, 'scope_code', 'UAH'),
            )));

        $currency = $this->getMock('\Magento\Directory\Model\Currency', [], [], '', false);
        $currency->expects($this->any())->method('load')->with($currencyCode)->will($this->returnSelf());

        $currencyFactory = $this->getMock(
            '\Magento\Directory\Model\CurrencyFactory',
            array('create', 'load')
        );
        $currencyFactory->expects($this->any())->method('create')->will($this->returnValue($currency));

        $appState = $this->getMock('\Magento\Framework\App\State', [], [], '', false);
        $appState->expects($this->any())->method('isInstalled')->will($this->returnValue(true));
        /** @var \Magento\Store\Model\Store $model */
        $model = $this->objectManagerHelper->getObject('Magento\Store\Model\Store',
            array('currencyFactory' => $currencyFactory, 'config' => $config, 'appState' => $appState)
        );
        $model->setCode('scope_code');
        $this->assertEquals($currency, $model->getBaseCurrency());
    }

    /**
     * @return array
     */
    public function getBaseCurrencyDataProvider()
    {
        return array(
            array(0, 'USD'),
            array(1, 'UAH'),
        );
    }
    
    public function testGetAllowedCurrencies()
    {
        $currencyPath = 'cur/ren/cy/path';
        $expectedResult = array('EUR', 'USD');

        $configMock = $this->getMockForAbstractClass(
            'Magento\Framework\App\Config\ReinitableConfigInterface',
            [],
            '',
            false
        );
        $configMock->expects($this->once())
            ->method('getValue')
            ->with($currencyPath, 'store', null)
            ->will($this->returnValue('EUR,USD'));

        /** @var \Magento\Store\Model\Store $model */
        $model = $this->objectManagerHelper->getObject('Magento\Store\Model\Store', array(
            'config' => $configMock,
            'currencyInstalled' => $currencyPath,
        ));

        $this->assertEquals($expectedResult, $model->getAllowedCurrencies());
    }
}

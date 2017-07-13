<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework;

use Zend\Stdlib\Parameters;

class UrlTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Framework\Url::class
        );
    }

    public function testSetGetUseSession()
    {
        $this->assertTrue((bool)$this->_model->getUseSession());
        $this->_model->setUseSession(false);
        $this->assertFalse($this->_model->getUseSession());
    }

    public function testSetRouteFrontName()
    {
        $value = 'route';
        $this->_model->setRouteFrontName($value);
        $this->assertEquals($value, $this->_model->getData('route_front_name'));
    }

    public function testGetConfigData()
    {
        $this->assertEquals('http://localhost/', $this->_model->getConfigData('base_url'));
    }

    /**
     * Note: isolation should be raised to flush the URL memory cache maintained by the store model
     * @magentoAppIsolation enabled
     */
    public function testGetBaseUrlDefaults()
    {
        $this->assertEquals('http://localhost/index.php/', $this->_model->getBaseUrl());
    }

    /**
     * Note: isolation flushes the URL memory cache
     * @magentoAppIsolation enabled
     * @magentoConfigFixture current_store web/seo/use_rewrites 1
     */
    public function testGetBaseUrlSeoRewrites()
    {
        $this->assertEquals('http://localhost/', $this->_model->getBaseUrl());
    }

    /**
     * Note: isolation flushes the URL memory cache
     * @magentoAppIsolation enabled
     *
     * @dataProvider getBaseUrlConfiguredDataProvider
     *
     * @magentoConfigFixture current_store web/secure/base_url http://sample.com/base_path/
     * @magentoConfigFixture current_store web/unsecure/base_link_url http://sample.com/base_link_path/
     * @magentoConfigFixture current_store web/secure/base_link_url https://sample.com/base_link_path/
     * @magentoConfigFixture current_store web/secure/use_in_frontend 1
     *
     * @param array $params
     * @param string $expectedUrl
     */
    public function testGetBaseUrlConfigured($params, $expectedUrl)
    {
        $actualUrl = $this->_model->getBaseUrl($params);
        $this->assertEquals($expectedUrl, $actualUrl);
    }

    /**
     * Check that url type is restored to default after call getBaseUrl with type specified in params
     */
    public function testGetBaseUrlWithTypeRestoring()
    {
        /**
         * Get base URL with default type
         */
        $this->assertEquals('http://localhost/index.php/', $this->_model->getBaseUrl(), 'Incorrect link url');

        /**
         * Set specified type
         */
        $webUrl = $this->_model->getBaseUrl(['_type' => \Magento\Framework\UrlInterface::URL_TYPE_WEB]);
        $this->assertEquals('http://localhost/', $webUrl, 'Incorrect web url');
        $this->assertEquals('http://localhost/index.php/', $this->_model->getBaseUrl(), 'Incorrect link url');

        /**
         * Get url with type specified in params
         */
        $mediaUrl = $this->_model->getBaseUrl(['_type' => \Magento\Framework\UrlInterface::URL_TYPE_MEDIA]);
        $this->assertEquals('http://localhost/pub/media/', $mediaUrl, 'Incorrect media url');
        $this->assertEquals('http://localhost/index.php/', $this->_model->getBaseUrl(), 'Incorrect link url');
    }

    public function getBaseUrlConfiguredDataProvider()
    {
        return [
            [['_type' => \Magento\Framework\UrlInterface::URL_TYPE_WEB], 'http://sample.com/base_path/'],
            [
                ['_type' => \Magento\Framework\UrlInterface::URL_TYPE_LINK],
                'http://sample.com/base_link_path/index.php/'
            ],
            [
                ['_type' => \Magento\Framework\UrlInterface::URL_TYPE_LINK, '_secure' => 1],
                'https://sample.com/base_link_path/index.php/'
            ]
        ];
    }

    public function testSetGetRouteName()
    {
        $this->_model->setRouteName('catalog');
        $this->assertEquals('catalog', $this->_model->getRouteName());

        $this->markTestIncomplete('setRouteName() logic is unclear.');
    }

    public function testSetGetControllerName()
    {
        $this->_model->setControllerName('product');
        $this->assertEquals('product', $this->_model->getControllerName());

        $this->markTestIncomplete('setControllerName() logic is unclear.');
    }

    public function testSetGetActionName()
    {
        $this->_model->setActionName('view');
        $this->assertEquals('view', $this->_model->getActionName());

        $this->markTestIncomplete('setActionName() logic is unclear.');
    }

    /**
     * Note: isolation flushes the URL memory cache
     * @magentoAppIsolation enabled
     */
    public function testGetRouteUrl()
    {
        $this->assertEquals('http://localhost/index.php/', $this->_model->getRouteUrl());
        $this->assertEquals(
            'http://localhost/index.php/catalog/product/view/id/50/',
            $this->_model->getRouteUrl('catalog/product/view', ['id' => 50])
        );
        $this->assertEquals(
            'http://localhost/index.php/fancy_uri',
            $this->_model->getRouteUrl('core/index/index', ['_direct' => 'fancy_uri'])
        );
    }

    public function testSetGetFragment()
    {
        $this->_model->setFragment('value');
        $this->assertEquals('value', $this->_model->getFragment());
    }

    /**
     * Note: isolation flushes the URL memory cache
     * @magentoAppIsolation enabled
     */
    public function testGetUrl()
    {
        $result = $this->_model->getUrl(
            'catalog/product/view',
            ['_fragment' => 'anchor', '_escape' => 1, '_query' => 'foo=bar', '_nosid' => 1, 'id' => 100]
        );
        $this->assertEquals('http://localhost/index.php/catalog/product/view/id/100/?foo=bar#anchor', $result);
    }

    /**
     * Note: isolation flushes the URL memory cache
     * @magentoAppIsolation enabled
     */
    public function testGetUrlDoesntAddQueryParamsOnConsequentCalls()
    {
        $result = $this->_model->getUrl('catalog/product/view', ['_query' => 'foo=bar', '_nosid' => 1]);
        $this->assertEquals('http://localhost/index.php/catalog/product/view/?foo=bar', $result);
        $result = $this->_model->getUrl('catalog/product/view', ['_nosid' => 1]);
        $this->assertEquals('http://localhost/index.php/catalog/product/view/', $result);
    }

    /**
     * Note: isolation flushes the URL memory cache
     * @magentoAppIsolation enabled
     * @covers \Magento\Framework\Url::getUrl
     */
    public function testGetUrlDoesntAddFragmentOnConsequentCalls()
    {
        $result = $this->_model->getUrl('catalog/product/view', ['_nosid' => 1, '_fragment' => 'section']);
        $this->assertEquals('http://localhost/index.php/catalog/product/view/#section', $result);
        $result = $this->_model->getUrl('catalog/product/view', ['_nosid' => 1]);
        $this->assertEquals('http://localhost/index.php/catalog/product/view/', $result);
    }

    /**
     * Note: isolation flushes the URL memory cache
     * @magentoAppIsolation enabled
     *
     * @dataProvider consequentCallsDataProvider
     *
     * @param string $firstCallUrl
     * @param string $secondCallUrl
     * @param array $firstRouteParams
     * @param array $secondRouteParams
     * @param string $firstExpectedUrl
     * @param string $secondExpectedUrl
     * @covers \Magento\Framework\Url::getUrl
     */
    public function testGetUrlOnConsequentCalls(
        $firstCallUrl,
        $secondCallUrl,
        $firstRouteParams,
        $secondRouteParams,
        $firstExpectedUrl,
        $secondExpectedUrl
    ) {
        $result = $this->_model->getUrl($firstCallUrl, $firstRouteParams);
        $this->assertEquals($firstExpectedUrl, $result);

        $result = $this->_model->getUrl($secondCallUrl, $secondRouteParams);
        $this->assertEquals($secondExpectedUrl, $result);
    }

    /**
     * Data provider for testGetUrlOnConsequentCalls()
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @return array
     */
    public function consequentCallsDataProvider()
    {
        return [
            [
                'r_1/c_1/a_1/p_1/v_1',
                'r_1/c_1/a_1/p_1/v_1',
                null,
                null,
                'http://localhost/index.php/r_1/c_1/a_1/p_1/v_1/',
                'http://localhost/index.php/r_1/c_1/a_1/p_1/v_1/',
            ],
            [
                'r_1/c_1/a_1/p_1/v_1',
                'r_1/c_1/a_1/p_1/v_2',
                null,
                null,
                'http://localhost/index.php/r_1/c_1/a_1/p_1/v_1/',
                'http://localhost/index.php/r_1/c_1/a_1/p_1/v_2/'
            ],
            [
                'r_1/c_1/a_1/p_1/v_1',
                'r_1/c_1/a_1/p_1',
                null,
                null,
                'http://localhost/index.php/r_1/c_1/a_1/p_1/v_1/',
                'http://localhost/index.php/r_1/c_1/a_1/'
            ],
            [
                'r_1/c_1/a_1/p_1/v_1',
                'r_1/c_1/a_1/p_2/v_2',
                null,
                null,
                'http://localhost/index.php/r_1/c_1/a_1/p_1/v_1/',
                'http://localhost/index.php/r_1/c_1/a_1/p_2/v_2/'
            ],
            [
                'r_1/c_1/a_1/p_1/v_1',
                'r_1/c_1/a_1',
                null,
                null,
                'http://localhost/index.php/r_1/c_1/a_1/p_1/v_1/',
                'http://localhost/index.php/r_1/c_1/a_1/'
            ],
            [
                'r_1/c_1/a_1/p_1/v_1',
                'r_1/c_1/a_2',
                null,
                null,
                'http://localhost/index.php/r_1/c_1/a_1/p_1/v_1/',
                'http://localhost/index.php/r_1/c_1/a_2/'
            ],
            [
                'r_1/c_1/a_1/p_1/v_1',
                'r_1/c_1',
                null,
                null,
                'http://localhost/index.php/r_1/c_1/a_1/p_1/v_1/',
                'http://localhost/index.php/r_1/c_1/'
            ],
            [
                'r_1/c_1/a_1/p_1/v_1',
                'r_1/c_2',
                null,
                null,
                'http://localhost/index.php/r_1/c_1/a_1/p_1/v_1/',
                'http://localhost/index.php/r_1/c_2/'
            ],
            [
                'r_1/c_1/a_1/p_1/v_1',
                'r_1',
                null,
                null,
                'http://localhost/index.php/r_1/c_1/a_1/p_1/v_1/',
                'http://localhost/index.php/r_1/'
            ],
            [
                'r_1/c_1/a_1/p_1/v_1',
                'r_2',
                null,
                null,
                'http://localhost/index.php/r_1/c_1/a_1/p_1/v_1/',
                'http://localhost/index.php/r_2/'
            ],
            [
                'r_1/c_1/a_1/p_1/v_1',
                null,
                null,
                null,
                'http://localhost/index.php/r_1/c_1/a_1/p_1/v_1/',
                'http://localhost/index.php/'
            ],
            [
                'r_1/c_1/a_1',
                'r_1/c_1/a_1/p_1/v_1',
                null,
                null,
                'http://localhost/index.php/r_1/c_1/a_1/',
                'http://localhost/index.php/r_1/c_1/a_1/p_1/v_1/'
            ],
            [
                null,
                'r_1/c_1/a_1',
                null,
                null,
                'http://localhost/index.php/',
                'http://localhost/index.php/r_1/c_1/a_1/'
            ],
            [
                'r_1/c_1/a_1/p_1/v_1',
                'r_1/c_1/a_1/p_1/v_1',
                ['p_2' => 'v_2'],
                ['p_2' => 'v_2'],
                'http://localhost/index.php/r_1/c_1/a_1/p_1/v_1/p_2/v_2/',
                'http://localhost/index.php/r_1/c_1/a_1/p_1/v_1/p_2/v_2/'
            ],
            [
                'r_1/c_1/a_1/p_1/v_1',
                'r_1/c_1/a_1',
                ['p_2' => 'v_2'],
                ['p_2' => 'v_2'],
                'http://localhost/index.php/r_1/c_1/a_1/p_1/v_1/p_2/v_2/',
                'http://localhost/index.php/r_1/c_1/a_1/p_2/v_2/'
            ],
            [
                'r_1/c_1/a_1/p_1/v_1',
                null,
                ['p_2' => 'v_2'],
                ['p_1' => 'v_1', 'p_2' => 'v_2'],
                'http://localhost/index.php/r_1/c_1/a_1/p_1/v_1/p_2/v_2/',
                'http://localhost/index.php/p_1/v_1/p_2/v_2/'
            ]
        ];
    }

    public function testEscape()
    {
        $this->assertEquals('%22%27%3E%3C', $this->_model->escape('"\'><'));
    }

    /**
     * Note: isolation flushes the URL memory cache
     * @magentoAppIsolation enabled
     */
    public function testGetDirectUrl()
    {
        $directUrl = $this->_model->getDirectUrl('fancy_uri', ['_query' => ['foo' => 'bar']]);
        $this->assertEquals('http://localhost/index.php/fancy_uri?foo=bar', $directUrl);
    }

    /**
     * Note: isolation flushes the URL memory cache
     * @magentoAppIsolation enabled
     *
     * Note: to enforce SID in URLs, base URL must be different from the current $_SERVER['HTTP_HOST']
     * @magentoConfigFixture current_store web/unsecure/base_link_url http://domain.com/
     */
    public function testSessionUrlVar()
    {
        $sessionId = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Framework\Session\Generic::class
        )->getSessionId();
        $sessionUrl = $this->_model->sessionUrlVar('<a href="http://example.com/?___SID=U">www.example.com</a>');
        $this->assertEquals('<a href="http://example.com/?SID=' . $sessionId . '">www.example.com</a>', $sessionUrl);
    }

    public function testUseSessionIdForUrl()
    {
        $_SERVER['HTTP_HOST'] = 'localhost';
        $this->assertFalse($this->_model->useSessionIdForUrl(true));
        $this->assertFalse($this->_model->useSessionIdForUrl(false));
    }

    /**
     * Note: isolation flushes the URL memory cache
     * @magentoAppIsolation enabled
     */
    public function testIsOwnOriginUrl()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var $request \Magento\TestFramework\Request */
        $request = $objectManager->get(\Magento\Framework\App\RequestInterface::class);
        $request->setServer(new Parameters(['HTTP_REFERER' => 'http://localhost/']));
        $this->assertTrue($this->_model->isOwnOriginUrl());

        $request->setServer(new Parameters(['HTTP_REFERER' => 'http://example.com/']));
        $this->assertFalse($this->_model->isOwnOriginUrl());
    }
}

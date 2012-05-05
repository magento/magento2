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
 * @category    Magento
 * @package     Mage_Core
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Core_Model_UrlTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Core_Model_Url
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = new Mage_Core_Model_Url;
    }

    public function testParseUrl()
    {
        $url = 'http://user:password@www.example.com:80/manual/3.5/?one=1&two=2#skeleton-generator.test';
        $this->assertInstanceOf(get_class($this->_model), $this->_model->parseUrl($url));
        $this->assertEquals('http', $this->_model->getScheme());
        $this->assertEquals('www.example.com', $this->_model->getHost());
        $this->assertEquals('80', $this->_model->getPort());
        $this->assertEquals('user', $this->_model->getUser());
        $this->assertEquals('password', $this->_model->getPassword());
        $this->assertEquals('/manual/3.5/', $this->_model->getPath());
        $this->assertEquals('one=1&two=2', $this->_model->getQuery());
        $this->assertEquals('skeleton-generator.test', $this->_model->getFragment());
    }

    public function testGetDefaultControllerName()
    {
        $this->assertEquals('index', $this->_model->getDefaultControllerName());
    }

    public function testSetUseUrlCache()
    {
        $value = '2';
        $this->_model->setUseUrlCache($value);
        $this->assertEquals($value, $this->_model->getData('use_url_cache'));
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

    public function testGetDefaultActionName()
    {
        $this->assertEquals('index', $this->_model->getDefaultActionName());
    }

    public function testGetConfigData()
    {
        $this->assertEquals('http://localhost/', $this->_model->getConfigData('base_url'));
    }

    public function testSetGetRequest()
    {
        $this->assertInstanceOf('Zend_Controller_Request_Http', $this->_model->getRequest());
        $request = new Magento_Test_Request;
        $this->_model->setRequest($request);
        $this->assertSame($request, $this->_model->getRequest());
    }

    public function testGetType()
    {
        $this->assertEquals(Mage_Core_Model_Store::URL_TYPE_LINK, $this->_model->getType());
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testGetSecure()
    {
        $this->assertFalse($this->_model->getSecure());
        $this->_model->setSecureIsForced(1);
        $this->assertTrue(is_bool($this->_model->getSecure()));
        Mage::app()->getStore()->setId(Mage_Core_Model_App::ADMIN_STORE_ID);
        $this->assertFalse($this->_model->getSecure());
    }

    public function testSetGetStore()
    {
        $this->assertInstanceOf('Mage_Core_Model_Store', $this->_model->getStore());

        $store = new Mage_Core_Model_Store;
        $this->_model->setStore($store);
        $this->assertSame($store, $this->_model->getStore());
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
     * @magentoConfigFixture current_store web/secure/base_url        http://sample.com/base_path/
     * @magentoConfigFixture current_store web/secure/base_link_url   https://sample.com/base_link_path/
     * @magentoConfigFixture current_store web/secure/use_in_frontend 1
     */
    public function testGetBaseUrlConfigured()
    {
        $actualUrl = $this->_model->getBaseUrl(array('_type' => Mage_Core_Model_Store::URL_TYPE_WEB));
        $this->assertEquals('http://sample.com/base_path/', $actualUrl);

        $actualUrl = $this->_model->getBaseUrl(array('_type' => Mage_Core_Model_Store::URL_TYPE_LINK));
        $this->assertEquals('https://sample.com/base_link_path/index.php/', $actualUrl);

        $actualUrl = $this->_model->getBaseUrl(array('_type' => Mage_Core_Model_Store::URL_TYPE_LINK, '_secure' => 1));
        $this->assertEquals('https://sample.com/base_link_path/index.php/', $actualUrl);
    }

    public function testSetRoutePath()
    {
        // *
        $this->_model->setRoutePath('catalog');
        $this->assertEquals('catalog', $this->_model->getRouteName());

        // */*
        $this->_model->setRoutePath('catalog/product');
        $this->assertEquals('catalog', $this->_model->getRouteName());
        $this->assertEquals('product', $this->_model->getControllerName());

        // */*/*
        $this->_model->setRoutePath('catalog/product/view');
        $this->assertEquals('catalog', $this->_model->getRouteName());
        $this->assertEquals('product', $this->_model->getControllerName());
        $this->assertEquals('view', $this->_model->getActionName());

        // */*/*/param/value
        $this->_model->setRoutePath('catalog/product/view/id/50');
        $this->assertEquals('catalog', $this->_model->getRouteName());
        $this->assertEquals('product', $this->_model->getControllerName());
        $this->assertEquals('view', $this->_model->getActionName());
        $this->assertEquals('50', $this->_model->getRouteParam('id'));
    }

    public function testGetActionPath()
    {
        $this->assertEquals('', $this->_model->getActionPath());

        $this->_model->setRoutePath('catalog/product/view/id/50');
        $this->assertEquals('catalog/product/view/', $this->_model->getActionPath());
    }

    public function testGetRoutePath()
    {
        $this->assertEquals('', $this->_model->getRoutePath());

        $this->_model->setRoutePath('catalog/product/view/id/50');
        $this->assertEquals('catalog/product/view/id/50/', $this->_model->getRoutePath());
    }

    public function testSetGetRouteName()
    {
        $this->_model->setRouteName('catalog');
        $this->assertEquals('catalog', $this->_model->getRouteName());

        $this->markTestIncomplete('setRouteName() logic is unclear.');
    }

    public function testGetRouteFrontName()
    {
        $this->_model->setRouteName('catalog');
        $this->assertEquals('catalog', $this->_model->getRouteFrontName());
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

    public function testSetGetRouteParams()
    {
        $this->_model->setRouteParams(array(
            '_type' => 1,
            '_store' => 1,
            '_forced_secure' => 1,
            '_absolute' => 1,
            '_current' => 0,
            '_use_rewrite' => 1,
            '_store_to_url' => true,
            'param1' => 'value1',
        ));
        $this->assertEquals(array('param1' => 'value1'), $this->_model->getRouteParams());

        $this->_model->setRouteParams(array('param2' => 'value2'), false);
        $this->assertEquals(array('param1' => 'value1', 'param2' => 'value2'), $this->_model->getRouteParams());
    }

    public function testSetGetRouteParam()
    {
        $this->_model->setRouteParam('id', 100);
        $this->assertEquals(100, $this->_model->getRouteParam('id'));
        $this->_model->setRouteParam('parent_id', 50);
        $this->assertEquals(array('id' => 100, 'parent_id' => 50), $this->_model->getRouteParams());
    }

    /**
     * Note: isolation flushes the URL memory cache
     * @magentoAppIsolation enabled
     */
    public function testGetRouteUrl()
    {
        $this->assertEquals('http://localhost/index.php/', $this->_model->getRouteUrl());
        $this->assertEquals('http://localhost/index.php/catalog/product/view/id/50/',
            $this->_model->getRouteUrl('catalog/product/view', array('id' => 50))
        );
        $this->assertEquals('http://localhost/index.php/fancy_uri',
            $this->_model->getRouteUrl('core/index/index', array('_direct' => 'fancy_uri'))
        );
    }

    public function testSetGetQuery()
    {
        $this->_model->setQuery('one=1&two=2');
        $this->assertEquals('one=1&two=2', $this->_model->getQuery());

        // here comes the funny part
        $this->_model->unsQuery();
        $this->_model->setQueryParams(array('three' => 3, 'four' => 4));
        $this->assertEquals('four=4&amp;three=3', $this->_model->getQuery(true));
    }

    public function testSetGetPurgeQueryParams()
    {
        $params = array('one' => 1, 'two' => 2);
        $this->_model->setQueryParams($params);
        $this->assertEquals($params, $this->_model->getQueryParams());

        $this->_model->purgeQueryParams();
        $this->assertEquals(array(), $this->_model->getQueryParams());
    }

    public function testSetGetQueryParam()
    {
        $this->_model->setQueryParam('key', 'value');
        $this->assertEquals('value', $this->_model->getQueryParam('key'));
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
        $result = $this->_model->getUrl('catalog/product/view', array(
            '_fragment' => 'anchor',
            '_escape' => 1,
            '_query' => 'foo=bar',
            '_nosid' => 1,
            'id' => 100
        ));
        $this->assertEquals('http://localhost/index.php/catalog/product/view/id/100/?foo=bar#anchor', $result);
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
        $this->assertEquals('http://localhost/index.php/fancy_uri?foo=bar',
            $this->_model->getDirectUrl('fancy_uri', array('_query' => array('foo' => 'bar')))
        );
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
        $sessionId = Mage::getSingleton('Mage_Core_Model_Session')->getEncryptedSessionId();
        $this->assertEquals('<a href="http://example.com/?SID=' . $sessionId . '">www.example.com</a>',
            $this->_model->sessionUrlVar('<a href="http://example.com/?___SID=U">www.example.com</a>')
        );
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
    public function testSessionVarCallback()
    {
        $this->_model->setData('use_session_id_for_url_0', false);
        $this->_model->setData('use_session_id_for_url_1', false);

        // evidence of cyclomatic complexity
        $this->assertEquals('?', $this->_model->sessionVarCallback(array('', '?', '', '')));
        $this->assertEquals('', $this->_model->sessionVarCallback(array('', '?', '')));
        $this->assertEquals('', $this->_model->sessionVarCallback(array('', '&', '')));
        $this->assertEquals('', $this->_model->sessionVarCallback(array('', '&amp;', '')));
        $this->assertEquals('', $this->_model->sessionVarCallback(array('', '&', '', '')));
        $this->assertEquals('', $this->_model->sessionVarCallback(array('', '&amp;', '', '')));
    }

    /**
     * Note: isolation flushes the URL memory cache
     * @magentoAppIsolation enabled
     */
    public function testIsOwnOriginUrl()
    {
        $_SERVER['HTTP_REFERER'] = 'http://localhost/';
        $this->assertTrue($this->_model->isOwnOriginUrl());

        $_SERVER['HTTP_REFERER'] = 'http://example.com/';
        $this->assertFalse($this->_model->isOwnOriginUrl());
    }
}

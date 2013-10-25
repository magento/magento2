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
 * @package     Magento_Core
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Core\Model;

class AppTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Core\Model\App
     */
    protected $_model;

    /**
     * Application instance initialized with environment
     * Is used in some tests that require initialization
     *
     * @var \Magento\Core\Model\App
     */
    protected $_mageModel;

    /**
     * Callback test flag
     *
     * @var bool
     */
    protected $_errorCatchFlag = false;

    protected function setUp()
    {
        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Core\Model\App');
        $this->_mageModel = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Core\Model\App');
    }

    public function testGetCookie()
    {
        $this->assertInstanceOf('Magento\Core\Model\Cookie', $this->_model->getCookie());
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoConfigFixture current_store general/single_store_mode/enabled 1
     */
    public function testIsSingleStoreModeWhenEnabled()
    {
        $this->assertTrue($this->_mageModel->isSingleStoreMode());
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoConfigFixture current_store general/single_store_mode/enabled 0
     */
    public function testIsSingleStoreModeWhenDisabled()
    {
        $this->assertFalse($this->_mageModel->isSingleStoreMode());;
    }

    public function testHasSingleStore()
    {
        $this->assertTrue($this->_model->hasSingleStore());
        $this->assertTrue($this->_mageModel->hasSingleStore());
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testSetCurrentStore()
    {
        $store = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Core\Model\Store');
        $this->_model->setCurrentStore($store);
        $this->assertSame($store, $this->_model->getStore());
    }

    public function testSetErrorHandler()
    {
        $this->_model->setErrorHandler(array($this, 'errorHandler'));
        try {
            trigger_error('test', E_USER_NOTICE);
            if (!$this->_errorCatchFlag) {
                $this->fail('Error handler is not working');
            }
            restore_error_handler();
        } catch (\Exception $e) {
            restore_error_handler();
            throw $e;
        }
    }

    public function errorHandler()
    {
        $this->_errorCatchFlag = true;
    }

    public function testGetArea()
    {
        $area = $this->_model->getArea('frontend');
        $this->assertInstanceOf('Magento\Core\Model\App\Area', $area);
        $this->assertSame($area, $this->_model->getArea('frontend'));
    }

    /**
     * @expectedException \Magento\Core\Model\Store\Exception
     */
    public function testGetNotExistingStore()
    {
        $this->_mageModel->getStore(100);
    }

    public function testGetSafeNotExistingStore()
    {
        $this->_mageModel->getSafeStore(100);
        $request = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\App\Request\Http');
        $this->assertEquals('noRoute', $request->getActionName());
    }

    public function testGetStores()
    {
        $this->assertNotEmpty($this->_mageModel->getStores());
        $this->assertNotContains(\Magento\Core\Model\App::ADMIN_STORE_ID, array_keys($this->_mageModel->getStores()));
        $this->assertContains(\Magento\Core\Model\App::ADMIN_STORE_ID, array_keys($this->_mageModel->getStores(true)));
    }

    public function testGetDefaultStoreView()
    {
        $store = $this->_mageModel->getDefaultStoreView();
        $this->assertEquals('default', $store->getCode());
    }

    public function testGetDistroLocaleCode()
    {
        $this->assertEquals(\Magento\Core\Model\App::DISTRO_LOCALE_CODE, $this->_model->getDistroLocaleCode());
    }

    /**
     * @expectedException \Magento\Core\Exception
     */
    public function testGetWebsiteNonExisting()
    {
        $this->assertNotEmpty($this->_mageModel->getWebsite(true)->getId());
        $this->_mageModel->getWebsite(100);
    }

    public function testGetWebsites()
    {
        $this->assertNotEmpty($this->_mageModel->getWebsites());
        $this->assertNotContains(0, array_keys($this->_mageModel->getWebsites()));
        $this->assertContains(0, array_keys($this->_mageModel->getWebsites(true)));
    }

    /**
     * @expectedException \Magento\Core\Exception
     */
    public function testGetGroupNonExisting()
    {
        $this->assertNotEmpty($this->_mageModel->getGroup(true)->getId());
        $this->_mageModel->getGroup(100);
    }

    public function testGetLocale()
    {
        $locale = $this->_model->getLocale();
        $this->assertInstanceOf('Magento\Core\Model\LocaleInterface', $locale);
        $this->assertSame($locale, $this->_model->getLocale());
    }

    public function testGetBaseCurrencyCode()
    {
        $this->assertEquals('USD', $this->_model->getBaseCurrencyCode());
    }

    public function testGetFrontController()
    {
        $front = $this->_mageModel->getFrontController();
        $this->assertInstanceOf('Magento\App\FrontController', $front);
        $this->assertSame($front, $this->_mageModel->getFrontController());
    }

    public function testGetCacheInstance()
    {
        $cache = $this->_mageModel->getCacheInstance();
        $this->assertInstanceOf('Magento\Core\Model\CacheInterface', $cache);
        $this->assertSame($cache, $this->_mageModel->getCacheInstance());
    }

    public function testGetCache()
    {
        $this->assertInstanceOf('Magento\Cache\FrontendInterface', $this->_mageModel->getCache());
    }

    public function testLoadSaveRemoveCache()
    {
        $this->assertEmpty($this->_mageModel->loadCache('test_id'));
        $this->_mageModel->saveCache('test_data', 'test_id');
        $this->assertEquals('test_data', $this->_mageModel->loadCache('test_id'));
        $this->_mageModel->removeCache('test_id');
        $this->assertEmpty($this->_mageModel->loadCache('test_id'));
    }

    public function testCleanCache()
    {
        $this->assertEmpty($this->_mageModel->loadCache('test_id'));
        $this->_mageModel->saveCache('test_data', 'test_id', array('test_tag'));
        $this->assertEquals('test_data', $this->_mageModel->loadCache('test_id'));
        $this->_mageModel->cleanCache(array('test_tag'));
        $this->assertEmpty($this->_mageModel->loadCache('test_id'));
    }

    public function testSetGetRequest()
    {
        $this->assertInstanceOf('Magento\App\RequestInterface', $this->_model->getRequest());
        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var $request \Magento\TestFramework\Request */
        $request = $objectManager->get('Magento\TestFramework\Request');
        $this->_model->setRequest($request);
        $this->assertSame($request, $this->_model->getRequest());
    }

    public function testSetGetResponse()
    {
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get('Magento\App\ResponseInterface')->headersSentThrowsException = false;
        $this->assertInstanceOf('Magento\App\ResponseInterface', $this->_model->getResponse());
        $expectedHeader = array(
            'name' => 'Content-Type',
            'value' => 'text/html; charset=UTF-8',
            'replace' => false
        );
        $this->assertContains($expectedHeader, $this->_model->getResponse()->getHeaders());
        $response = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\App\ResponseInterface');
        $this->_model->setResponse($response);
        $this->assertSame($response, $this->_model->getResponse());
        $this->assertEmpty($this->_model->getResponse()->getHeaders());
    }

    /**
     * @expectedException \Magento\Core\Model\Store\Exception
     */
    public function testThrowStoreException()
    {
        $this->_model->throwStoreException('test');
    }

    public function testSetGetUseSessionVar()
    {
        $this->assertFalse($this->_model->getUseSessionVar());
        $this->_model->setUseSessionVar(true);
        $this->assertTrue($this->_model->getUseSessionVar());
    }

    public function testGetAnyStoreView()
    {
        $this->assertInstanceOf('Magento\Core\Model\Store', $this->_mageModel->getAnyStoreView());
    }

    public function testSetGetUseSessionInUrl()
    {
        $this->assertTrue($this->_model->getUseSessionInUrl());
        $this->_model->setUseSessionInUrl(false);
        $this->assertFalse($this->_model->getUseSessionInUrl());
    }

    public function testGetGroups()
    {
        $groups = $this->_mageModel->getGroups();
        $this->assertInternalType('array', $groups);
        $this->assertGreaterThanOrEqual(1, count($groups));
    }
}

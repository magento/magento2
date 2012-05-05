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

class Mage_Core_Model_AppTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Core_Model_App
     */
    protected $_model;

    /**
     * Application instance initialized with environment
     * Is used in some tests that require initialization
     *
     * @var Mage_Core_Model_App
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
        $this->_model       = new Mage_Core_Model_App;
        $this->_mageModel   = Mage::app();
    }

    public function testInit()
    {
        $this->assertNull($this->_model->getConfig());
        $this->_model->init('');
        $this->assertInstanceOf('Mage_Core_Model_Config', $this->_model->getConfig());
        $this->assertNotEmpty($this->_model->getConfig()->getNode());
        $this->assertContains(Mage_Core_Model_App::ADMIN_STORE_ID, array_keys($this->_model->getStores(true)));
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testRun()
    {
        if (!Magento_Test_Bootstrap::canTestHeaders()) {
            $this->markTestSkipped('Can\'t test application run without sending headers');
        }

        $_SERVER['HTTP_HOST'] = 'localhost';
        $this->_mageModel->getRequest()->setRequestUri('core/index/index');
        $this->_mageModel->run(array());
        $this->assertTrue($this->_mageModel->getRequest()->isDispatched());
    }

    public function testGetCookie()
    {
        $this->assertInstanceOf('Mage_Core_Model_Cookie', $this->_model->getCookie());
    }

    public function testIsSingleStoreMode()
    {
        $this->assertNull($this->_model->isSingleStoreMode());
        $this->assertTrue($this->_mageModel->isSingleStoreMode());
    }

    public function testSetCurrentStore()
    {
        $store = new Mage_Core_Model_Store();
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
        } catch (Exception $e) {
            restore_error_handler();
            throw $e;
        }
    }

    public function errorHandler()
    {
        $this->_errorCatchFlag = true;
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testLoadGetArea()
    {
        $this->_model->loadArea('frontend');
        $this->assertSame($this->_model, $this->_model->getArea('frontend')->getApplication());
    }

    /**
     * @expectedException Mage_Core_Model_Store_Exception
     */
    public function testGetNotExistingStore()
    {
        $this->_mageModel->getStore(100);
    }

    public function testGetSafeNotExistingStore()
    {
        $this->_mageModel->getSafeStore(100);
        $this->assertEquals('noRoute', $this->_mageModel->getRequest()->getActionName());
    }

    public function testGetStores()
    {
        $this->assertNotEmpty($this->_mageModel->getStores());
        $this->assertNotContains(Mage_Core_Model_App::ADMIN_STORE_ID, array_keys($this->_mageModel->getStores()));
        $this->assertContains(Mage_Core_Model_App::ADMIN_STORE_ID, array_keys($this->_mageModel->getStores(true)));
    }

    public function testGetDefaultStoreView()
    {
        $store = $this->_mageModel->getDefaultStoreView();
        $this->assertEquals('default', $store->getCode());
    }

    public function testGetDistroLocaleCode()
    {
        $this->assertEquals(Mage_Core_Model_App::DISTRO_LOCALE_CODE, $this->_model->getDistroLocaleCode());
    }

    /**
     * @expectedException Mage_Core_Exception
     */
    public function testGetWebsiteNonExisting()
    {
        $this->assertNotEmpty($this->_mageModel->getWebsite()->getId());
        $this->_mageModel->getWebsite(100);
    }

    public function testGetWebsites()
    {
        $this->assertNotEmpty($this->_mageModel->getWebsites());
        $this->assertNotContains(0, array_keys($this->_mageModel->getWebsites()));
        $this->assertContains(0, array_keys($this->_mageModel->getWebsites(true)));
    }

    /**
     * @expectedException Mage_Core_Exception
     */
    public function testGetGroupNonExisting()
    {
        $this->assertNotEmpty($this->_mageModel->getGroup()->getId());
        $this->_mageModel->getGroup(100);
    }

    public function testGetLocale()
    {
        $locale = $this->_model->getLocale();
        $this->assertInstanceOf('Mage_Core_Model_Locale', $locale);
        $this->assertSame($locale, $this->_model->getLocale());
    }

    public function testGetLayout()
    {
        $layout = $this->_mageModel->getLayout();
        $this->assertInstanceOf('Mage_Core_Model_Layout', $layout);
        $this->assertSame($layout, $this->_mageModel->getLayout());
    }

    public function testGetTranslator()
    {
        $translate = $this->_model->getTranslator();
        $this->assertInstanceOf('Mage_Core_Model_Translate', $translate);
        $this->assertSame($translate, $this->_model->getTranslator());
    }

    /**
     * @dataProvider getHelperDataProvider
     */
    public function testGetHelper($inputHelperName, $expectedClass)
    {
        $this->assertInstanceOf($expectedClass, $this->_model->getHelper($inputHelperName));
    }

    public function getHelperDataProvider()
    {
        return array(
            'class name'  => array('Mage_Core_Helper_Data', 'Mage_Core_Helper_Data'),
            'module name' => array('Mage_Core',             'Mage_Core_Helper_Data'),
        );
    }

    public function testGetBaseCurrencyCode()
    {
        $this->assertEquals('USD', $this->_model->getBaseCurrencyCode());
    }

    public function testGetConfig()
    {
        $this->assertNull($this->_model->getConfig());
        $this->assertInstanceOf('Mage_Core_Model_Config', $this->_mageModel->getConfig());
    }

    public function testGetFrontController()
    {
        $front = $this->_mageModel->getFrontController();
        $this->assertInstanceOf('Mage_Core_Controller_Varien_Front', $front);
        $this->assertSame($front, $this->_mageModel->getFrontController());
    }

    public function testGetCacheInstance()
    {
        $cache = $this->_mageModel->getCacheInstance();
        $this->assertInstanceOf('Mage_Core_Model_Cache', $cache);
        $this->assertSame($cache, $this->_mageModel->getCacheInstance());
    }

    public function testGetCache()
    {
        $this->assertInstanceOf('Zend_Cache_Core', $this->_mageModel->getCache());
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

    public function testUseCache()
    {
        $this->assertTrue($this->_mageModel->useCache('config'));
        $this->assertFalse($this->_mageModel->useCache('not_existing_type'));
    }

    public function testSetGetRequest()
    {
        $this->assertInstanceOf('Mage_Core_Controller_Request_Http', $this->_model->getRequest());
        $this->_model->setRequest(new Magento_Test_Request());
        $this->assertInstanceOf('Magento_Test_Request', $this->_model->getRequest());
    }

    public function testSetGetResponse()
    {
        if (!Magento_Test_Bootstrap::canTestHeaders()) {
            $this->markTestSkipped('Can\'t test get response without sending headers');
        }
        $this->assertInstanceOf('Mage_Core_Controller_Response_Http', $this->_model->getResponse());
        $this->_model->setResponse(new Magento_Test_Response());
        $this->assertInstanceOf('Magento_Test_Response', $this->_model->getResponse());
    }

    public function testSetGetUpdateMode()
    {
        $this->assertFalse($this->_model->getUpdateMode());
        $this->_model->setUpdateMode(true);
        $this->assertTrue($this->_model->getUpdateMode());
    }

    /**
     * @expectedException Mage_Core_Model_Store_Exception
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
        $this->assertInstanceOf('Mage_Core_Model_Store', $this->_mageModel->getAnyStoreView());
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

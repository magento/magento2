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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Core_Model_StoreTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Core_Model_Store|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_model;

    public function setUp()
    {
        $params = array(
            'eventDispatcher' => Mage::getObjectManager()->get('Mage_Core_Model_Event_Manager'),
            'cacheManager'    => Mage::getObjectManager()->get('Mage_Core_Model_Cache')
        );

        $this->_model = $this->getMock(
            'Mage_Core_Model_Store',
            array('getUrl'),
            $params
        );
    }

    protected function tearDown()
    {
        $this->_model = null;
    }

    /**
     * @dataProvider loadDataProvider
     */
    public function testLoad($loadId, $expectedId)
    {
        $this->_model->load($loadId);
        $this->assertEquals($expectedId, $this->_model->getId());
    }

    public function loadDataProvider()
    {
        return array(
            array(1, 1),
            array('default', 1),
            array('nostore',null),
        );
    }

    public function testSetGetConfig()
    {
        /* config operations require store to be loaded */
        $this->_model->load('default');
        $value = $this->_model->getConfig(Mage_Core_Model_Store::XML_PATH_STORE_IN_URL);
        $this->_model->setConfig(Mage_Core_Model_Store::XML_PATH_STORE_IN_URL, 'test');
        $this->assertEquals('test', $this->_model->getConfig(Mage_Core_Model_Store::XML_PATH_STORE_IN_URL));
        $this->_model->setConfig(Mage_Core_Model_Store::XML_PATH_STORE_IN_URL, $value);

        /* Call set before get */
        $this->_model->setConfig(Mage_Core_Model_Store::XML_PATH_USE_REWRITES, 1);
        $this->assertEquals(1, $this->_model->getConfig(Mage_Core_Model_Store::XML_PATH_USE_REWRITES));
        $this->_model->setConfig(Mage_Core_Model_Store::XML_PATH_USE_REWRITES, 0);
    }

    public function testSetGetWebsite()
    {
        $this->assertFalse($this->_model->getWebsite());
        $website = Mage::app()->getWebsite();
        $this->_model->setWebsite($website);
        $actualResult = $this->_model->getWebsite();
        $this->assertSame($website, $actualResult);
    }

    public function testSetGetGroup()
    {
        $this->assertFalse($this->_model->getGroup());
        $storeGroup = Mage::app()->getGroup();
        $this->_model->setGroup($storeGroup);
        $actualResult = $this->_model->getGroup();
        $this->assertSame($storeGroup, $actualResult);
    }

    /**
     * Isolation is enabled, as we pollute config with rewrite values
     *
     * @param string $type
     * @param bool $useRewrites
     * @param bool $useStoreCode
     * @param string $expected
     * @dataProvider getBaseUrlDataProvider
     * @magentoAppIsolation enabled
     */
    public function testGetBaseUrl($type, $useRewrites, $useStoreCode, $expected)
    {
        /* config operations require store to be loaded */
        $this->_model->load('default');
        $this->_model->setConfig(Mage_Core_Model_Store::XML_PATH_USE_REWRITES, $useRewrites);
        $this->_model->setConfig(Mage_Core_Model_Store::XML_PATH_STORE_IN_URL, $useStoreCode);

        $actual = $this->_model->getBaseUrl($type);
        $this->assertEquals($expected, $actual);
    }

    /**
     * @return array
     */
    public function getBaseUrlDataProvider()
    {
        return array(
            array(Mage_Core_Model_Store::URL_TYPE_WEB, false, false, 'http://localhost/'),
            array(Mage_Core_Model_Store::URL_TYPE_WEB, false, true,  'http://localhost/'),
            array(Mage_Core_Model_Store::URL_TYPE_WEB, true,  false, 'http://localhost/'),
            array(Mage_Core_Model_Store::URL_TYPE_WEB, true,  true,  'http://localhost/'),
            array(Mage_Core_Model_Store::URL_TYPE_LINK, false, false, 'http://localhost/index.php/'),
            array(Mage_Core_Model_Store::URL_TYPE_LINK, false, true,  'http://localhost/index.php/default/'),
            array(Mage_Core_Model_Store::URL_TYPE_LINK, true,  false, 'http://localhost/'),
            array(Mage_Core_Model_Store::URL_TYPE_LINK, true,  true,  'http://localhost/default/'),
            array(Mage_Core_Model_Store::URL_TYPE_DIRECT_LINK, false, false, 'http://localhost/index.php/'),
            array(Mage_Core_Model_Store::URL_TYPE_DIRECT_LINK, false, true,  'http://localhost/index.php/'),
            array(Mage_Core_Model_Store::URL_TYPE_DIRECT_LINK, true,  false, 'http://localhost/'),
            array(Mage_Core_Model_Store::URL_TYPE_DIRECT_LINK, true,  true,  'http://localhost/'),
            array(Mage_Core_Model_Store::URL_TYPE_LIB, false, false, 'http://localhost/pub/lib/'),
            array(Mage_Core_Model_Store::URL_TYPE_LIB, false, true,  'http://localhost/pub/lib/'),
            array(Mage_Core_Model_Store::URL_TYPE_LIB, true,  false, 'http://localhost/pub/lib/'),
            array(Mage_Core_Model_Store::URL_TYPE_LIB, true,  true,  'http://localhost/pub/lib/'),
            array(Mage_Core_Model_Store::URL_TYPE_MEDIA, false, false, 'http://localhost/pub/media/'),
            array(Mage_Core_Model_Store::URL_TYPE_MEDIA, false, true,  'http://localhost/pub/media/'),
            array(Mage_Core_Model_Store::URL_TYPE_MEDIA, true,  false, 'http://localhost/pub/media/'),
            array(Mage_Core_Model_Store::URL_TYPE_MEDIA, true,  true,  'http://localhost/pub/media/'),
        );
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testGetBaseUrlInPub()
    {
        Magento_Test_Helper_Bootstrap::getInstance()->reinitialize(array(
            Mage::PARAM_APP_URIS => array(Mage_Core_Model_Dir::PUB => '')
        ));
        $this->_model->load('default');

        $this->assertEquals(
            'http://localhost/lib/',
            $this->_model->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LIB)
        );
        $this->assertEquals(
            'http://localhost/media/',
            $this->_model->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA)
        );
    }

    /**
     * Isolation is enabled, as we pollute config with rewrite values
     *
     * @param string $type
     * @param bool $useCustomEntryPoint
     * @param bool $useStoreCode
     * @param string $expected
     * @dataProvider getBaseUrlForCustomEntryPointDataProvider
     * @magentoAppIsolation enabled
     */
    public function testGetBaseUrlForCustomEntryPoint($type, $useCustomEntryPoint, $useStoreCode, $expected)
    {
        /* config operations require store to be loaded */
        $this->_model->load('default');
        $this->_model->setConfig(Mage_Core_Model_Store::XML_PATH_USE_REWRITES, false);
        $this->_model->setConfig(Mage_Core_Model_Store::XML_PATH_STORE_IN_URL, $useStoreCode);

        // emulate custom entry point
        $_SERVER['SCRIPT_FILENAME'] = 'custom_entry.php';
        if ($useCustomEntryPoint) {
            Mage::register('custom_entry_point', true);
        }
        $actual = $this->_model->getBaseUrl($type);
        $this->assertEquals($expected, $actual);
    }

    /**
     * @return array
     */
    public function getBaseUrlForCustomEntryPointDataProvider()
    {
        return array(
            array(Mage_Core_Model_Store::URL_TYPE_LINK, false, false, 'http://localhost/custom_entry.php/'),
            array(Mage_Core_Model_Store::URL_TYPE_LINK, false, true,  'http://localhost/custom_entry.php/default/'),
            array(Mage_Core_Model_Store::URL_TYPE_LINK, true, false, 'http://localhost/index.php/'),
            array(Mage_Core_Model_Store::URL_TYPE_LINK, true, true,  'http://localhost/index.php/default/'),
            array(Mage_Core_Model_Store::URL_TYPE_DIRECT_LINK, false, false, 'http://localhost/custom_entry.php/'),
            array(Mage_Core_Model_Store::URL_TYPE_DIRECT_LINK, false, true,  'http://localhost/custom_entry.php/'),
            array(Mage_Core_Model_Store::URL_TYPE_DIRECT_LINK, true,  false, 'http://localhost/index.php/'),
            array(Mage_Core_Model_Store::URL_TYPE_DIRECT_LINK, true,  true,  'http://localhost/index.php/'),
        );
    }

    public function testGetDefaultCurrency()
    {
        /* currency operations require store to be loaded */
        $this->_model->load('default');
        $this->assertEquals($this->_model->getDefaultCurrencyCode(), $this->_model->getDefaultCurrency()->getCode());
    }

    /**
     * @todo refactor Mage_Core_Model_Store::getPriceFilter, it can return two different types
     */
    public function testGetPriceFilter()
    {
        $this->assertInstanceOf('Mage_Directory_Model_Currency_Filter', $this->_model->getPriceFilter());
    }

    public function testIsCanDelete()
    {
        $this->assertFalse($this->_model->isCanDelete());
        $this->_model->load(1);
        $this->assertFalse($this->_model->isCanDelete());
        $this->_model->setId(100);
        $this->assertTrue($this->_model->isCanDelete());
    }

    public function testGetCurrentUrl()
    {
        $this->_model->expects($this->any())
            ->method('getUrl')
            ->will($this->returnValue('http://localhost/index.php'));
        $this->assertStringEndsWith('default', $this->_model->getCurrentUrl());
        $this->assertStringEndsNotWith('default', $this->_model->getCurrentUrl(false));
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testCRUD()
    {
        $this->_model->setData(
            array(
                'code'          => 'test',
                'website_id'    => 1,
                'group_id'      => 1,
                'name'          => 'test name',
                'sort_order'    => 0,
                'is_active'     => 1
            )
        );

        /* emulate admin store */
        Mage::app()->getStore()->setId(Mage_Core_Model_AppInterface::ADMIN_STORE_ID);
        $crud = new Magento_Test_Entity($this->_model, array('name' => 'new name'));
        $crud->testCrud();
    }

    /**
     * @param array $badStoreData
     *
     * @dataProvider saveValidationDataProvider
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @expectedException Mage_Core_Exception
     */
    public function testSaveValidation($badStoreData)
    {
        $normalStoreData = array(
            'code'          => 'test',
            'website_id'    => 1,
            'group_id'      => 1,
            'name'          => 'test name',
            'sort_order'    => 0,
            'is_active'     => 1
        );
        $data = array_merge($normalStoreData, $badStoreData);

        $this->_model->setData($data);

        /* emulate admin store */
        Mage::app()->getStore()->setId(Mage_Core_Model_App::ADMIN_STORE_ID);
        $this->_model->save();
    }

    /**
     * @return array
     */
    public static function saveValidationDataProvider()
    {
        return array(
            'empty store name' => array(
                array('name' => '')
            ),
            'empty store code' => array(
                array('code' => '')
            ),
            'invalid store code' => array(
                array('code' => '^_^')
            ),
        );
    }

    /**
     * @magentoConfigFixture global/functional_limitation/max_store_count 1
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @expectedException Mage_Core_Exception
     * @expectedExceptionMessage You are using the maximum number of store views allowed.
     */
    public function testSaveValidationLimitation()
    {
        $this->_model->setData(
            array(
                'code'          => 'test',
                'website_id'    => 1,
                'group_id'      => 1,
                'name'          => 'test name',
                'sort_order'    => 0,
                'is_active'     => 1
            )
        );

        /* emulate admin store */
        Mage::app()->getStore()->setId(Mage_Core_Model_App::ADMIN_STORE_ID);
        $this->_model->save();
    }

    /**
     *
     * @dataProvider getUrlClassNameDataProvider
     * @param $urlClassName
     * @param $expectedModel
     */
    public function testGetUrlModel($urlClassName, $expectedModel)
    {
        $urlModel = $this->_model->setUrlClassName($urlClassName)
            ->getUrlModel();
        $this->assertEquals($expectedModel, get_class($urlModel));
    }

    public function getUrlClassNameDataProvider()
    {
        return array(
            array(
                null,'Mage_Core_Model_Url'
            ),
            array(
                'Mage_Core_Model_Url', 'Mage_Core_Model_Url'
            ),
            array(
                'Mage_Backend_Model_Url', 'Mage_Backend_Model_Url'
            ),
        );
    }
}

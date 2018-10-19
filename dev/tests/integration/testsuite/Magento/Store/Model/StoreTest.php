<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Store\Model;

use Magento\Framework\App\Bootstrap;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\UrlInterface;
use Zend\Stdlib\Parameters;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class StoreTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var array
     */
    protected $modelParams;

    /**
     * @var Store|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $model;

    protected function setUp()
    {
        $this->model = $this->_getStoreModel();
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Store
     */
    protected function _getStoreModel()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->modelParams = [
            'context' => $objectManager->get(\Magento\Framework\Model\Context::class),
            'registry' => $objectManager->get(\Magento\Framework\Registry::class),
            'extensionFactory' => $objectManager->get(\Magento\Framework\Api\ExtensionAttributesFactory::class),
            'customAttributeFactory' => $objectManager->get(\Magento\Framework\Api\AttributeValueFactory::class),
            'resource' => $objectManager->get(\Magento\Store\Model\ResourceModel\Store::class),
            'coreFileStorageDatabase' => $objectManager->get(\Magento\MediaStorage\Helper\File\Storage\Database::class),
            'configCacheType' => $objectManager->get(\Magento\Framework\App\Cache\Type\Config::class),
            'url' => $objectManager->get(\Magento\Framework\Url::class),
            'request' => $objectManager->get(\Magento\Framework\App\RequestInterface::class),
            'configDataResource' => $objectManager->get(\Magento\Config\Model\ResourceModel\Config\Data::class),
            'filesystem' => $objectManager->get(\Magento\Framework\Filesystem::class),
            'config' => $objectManager->get(\Magento\Framework\App\Config\ReinitableConfigInterface::class),
            'storeManager' => $objectManager->get(\Magento\Store\Model\StoreManager::class),
            'sidResolver' => $objectManager->get(\Magento\Framework\Session\SidResolverInterface::class),
            'httpContext' => $objectManager->get(\Magento\Framework\App\Http\Context::class),
            'session' => $objectManager->get(\Magento\Framework\Session\SessionManagerInterface::class),
            'currencyFactory' => $objectManager->get(\Magento\Directory\Model\CurrencyFactory::class),
            'information' => $objectManager->get(\Magento\Store\Model\Information::class),
            'currencyInstalled' => 'system/currency/installed',
            'groupRepository' => $objectManager->get(\Magento\Store\Api\GroupRepositoryInterface::class),
            'websiteRepository' => $objectManager->get(\Magento\Store\Api\WebsiteRepositoryInterface::class),
        ];

        return $this->getMockBuilder(\Magento\Store\Model\Store::class)
            ->setMethods(['getUrl'])
            ->setConstructorArgs($this->modelParams)
            ->getMock();
    }

    protected function tearDown()
    {
        $this->model = null;
    }

    /**
     * @param $loadId
     * @param $expectedId
     * @dataProvider loadDataProvider
     */
    public function testLoad($loadId, $expectedId)
    {
        $this->model->load($loadId);
        $this->assertEquals($expectedId, $this->model->getId());
    }

    /**
     * @return array
     */
    public function loadDataProvider()
    {
        return [[1, 1], ['default', 1], ['nostore', null]];
    }

    public function testSetGetWebsite()
    {
        $this->assertFalse($this->model->getWebsite());
        $website = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Store\Model\StoreManagerInterface::class
        )->getWebsite();
        $this->model->setWebsite($website);
        $actualResult = $this->model->getWebsite();
        $this->assertSame($website, $actualResult);
    }

    public function testSetGetGroup()
    {
        $this->assertFalse($this->model->getGroup());
        $storeGroup = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Store\Model\StoreManager::class
        )->getGroup();
        $this->model->setGroup($storeGroup);
        $actualResult = $this->model->getGroup();
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
        $this->model->load('default');
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get(\Magento\Framework\App\Config\MutableScopeConfigInterface::class)
            ->setValue(Store::XML_PATH_USE_REWRITES, $useRewrites, ScopeInterface::SCOPE_STORE);

        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get(\Magento\Framework\App\Config\MutableScopeConfigInterface::class)
            ->setValue(Store::XML_PATH_STORE_IN_URL, $useStoreCode, ScopeInterface::SCOPE_STORE);

        $actual = $this->model->getBaseUrl($type);
        $this->assertEquals($expected, $actual);
    }

    /**
     * @return array
     */
    public function getBaseUrlDataProvider()
    {
        return [
            [UrlInterface::URL_TYPE_WEB, false, false, 'http://localhost/'],
            [UrlInterface::URL_TYPE_WEB, false, true, 'http://localhost/'],
            [UrlInterface::URL_TYPE_WEB, true, false, 'http://localhost/'],
            [UrlInterface::URL_TYPE_WEB, true, true, 'http://localhost/'],
            [UrlInterface::URL_TYPE_LINK, false, false, 'http://localhost/index.php/'],
            [UrlInterface::URL_TYPE_LINK, false, true, 'http://localhost/index.php/default/'],
            [UrlInterface::URL_TYPE_LINK, true, false, 'http://localhost/'],
            [UrlInterface::URL_TYPE_LINK, true, true, 'http://localhost/default/'],
            [UrlInterface::URL_TYPE_DIRECT_LINK, false, false, 'http://localhost/index.php/'],
            [UrlInterface::URL_TYPE_DIRECT_LINK, false, true, 'http://localhost/index.php/'],
            [UrlInterface::URL_TYPE_DIRECT_LINK, true, false, 'http://localhost/'],
            [UrlInterface::URL_TYPE_DIRECT_LINK, true, true, 'http://localhost/'],
            [UrlInterface::URL_TYPE_STATIC, false, false, 'http://localhost/pub/static/'],
            [UrlInterface::URL_TYPE_STATIC, false, true, 'http://localhost/pub/static/'],
            [UrlInterface::URL_TYPE_STATIC, true, false, 'http://localhost/pub/static/'],
            [UrlInterface::URL_TYPE_STATIC, true, true, 'http://localhost/pub/static/'],
            [UrlInterface::URL_TYPE_MEDIA, false, false, 'http://localhost/pub/media/'],
            [UrlInterface::URL_TYPE_MEDIA, false, true, 'http://localhost/pub/media/'],
            [UrlInterface::URL_TYPE_MEDIA, true, false, 'http://localhost/pub/media/'],
            [UrlInterface::URL_TYPE_MEDIA, true, true, 'http://localhost/pub/media/']
        ];
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testGetBaseUrlInPub()
    {
        \Magento\TestFramework\Helper\Bootstrap::getInstance()->reinitialize([
            Bootstrap::INIT_PARAM_FILESYSTEM_DIR_PATHS => [
                DirectoryList::PUB => [DirectoryList::URL_PATH => ''],
            ],
        ]);

        $this->model = $this->_getStoreModel();
        $this->model->load('default');

        $this->assertEquals('http://localhost/pub/static/', $this->model->getBaseUrl(UrlInterface::URL_TYPE_STATIC));
        $this->assertEquals('http://localhost/pub/media/', $this->model->getBaseUrl(UrlInterface::URL_TYPE_MEDIA));
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
        $this->model->load('default');
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get(\Magento\Framework\App\Config\MutableScopeConfigInterface::class)
            ->setValue(Store::XML_PATH_USE_REWRITES, false, ScopeInterface::SCOPE_STORE);

        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get(\Magento\Framework\App\Config\MutableScopeConfigInterface::class)
            ->setValue(Store::XML_PATH_STORE_IN_URL, $useStoreCode, ScopeInterface::SCOPE_STORE);

        // emulate custom entry point
        $_SERVER['SCRIPT_FILENAME'] = 'custom_entry.php';
        if ($useCustomEntryPoint) {
            $property = new \ReflectionProperty($this->model, '_isCustomEntryPoint');
            $property->setAccessible(true);
            $property->setValue($this->model, $useCustomEntryPoint);
        }
        $actual = $this->model->getBaseUrl($type);
        $this->assertEquals($expected, $actual);
    }

    /**
     * @return array
     */
    public function getBaseUrlForCustomEntryPointDataProvider()
    {
        return [
            [UrlInterface::URL_TYPE_LINK, false, false, 'http://localhost/custom_entry.php/'],
            [
                UrlInterface::URL_TYPE_LINK,
                false,
                true,
                'http://localhost/custom_entry.php/default/'
            ],
            [UrlInterface::URL_TYPE_LINK, true, false, 'http://localhost/index.php/'],
            [UrlInterface::URL_TYPE_LINK, true, true, 'http://localhost/index.php/default/'],
            [
                UrlInterface::URL_TYPE_DIRECT_LINK,
                false,
                false,
                'http://localhost/custom_entry.php/'
            ],
            [
                UrlInterface::URL_TYPE_DIRECT_LINK,
                false,
                true,
                'http://localhost/custom_entry.php/'
            ],
            [UrlInterface::URL_TYPE_DIRECT_LINK, true, false, 'http://localhost/index.php/'],
            [UrlInterface::URL_TYPE_DIRECT_LINK, true, true, 'http://localhost/index.php/']
        ];
    }

    public function testGetDefaultCurrency()
    {
        /* currency operations require store to be loaded */
        $this->model->load('default');
        $this->assertEquals($this->model->getDefaultCurrencyCode(), $this->model->getDefaultCurrency()->getCode());
    }

    public function testIsCanDelete()
    {
        $this->assertFalse($this->model->isCanDelete());
        $this->model->load(1);
        $this->assertFalse($this->model->isCanDelete());
        $this->model->setId(100);
        $this->assertFalse($this->model->isCanDelete());
    }

    public function testGetCurrentUrl()
    {
        $this->model->load('admin');
        $this->model->expects($this->any())->method('getUrl')->will($this->returnValue('http://localhost/index.php'));
        $this->assertStringEndsWith('default', $this->model->getCurrentUrl());
        $this->assertStringEndsNotWith('default', $this->model->getCurrentUrl(false));
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoAppArea adminhtml
     * @magentoDbIsolation enabled
     */
    public function testCRUD()
    {
        $this->model->setData([
            'code' => 'test',
            'website_id' => 1,
            'group_id' => 1,
            'name' => 'test name',
            'sort_order' => 0,
            'is_active' => 1,
        ]);
        $crud = new \Magento\TestFramework\Entity(
            $this->model,
            ['name' => 'new name'],
            \Magento\Store\Model\Store::class
        );
        $crud->testCrud();
    }

    /**
     * @param array $badStoreData
     *
     * @dataProvider saveValidationDataProvider
     * @magentoAppIsolation enabled
     * @magentoAppArea adminhtml
     * @magentoDbIsolation enabled
     * @expectedException \Magento\Framework\Exception\LocalizedException
     */
    public function testSaveValidation($badStoreData)
    {
        $normalStoreData = [
            'code' => 'test',
            'website_id' => 1,
            'group_id' => 1,
            'name' => 'test name',
            'sort_order' => 0,
            'is_active' => 1,
        ];
        $data = array_merge($normalStoreData, $badStoreData);
        $this->model->setData($data);
        $this->model->save();
    }

    /**
     * @return array
     */
    public static function saveValidationDataProvider()
    {
        return [
            'empty store name' => [['name' => '']],
            'empty store code' => [['code' => '']],
            'invalid store code' => [['code' => '^_^']]
        ];
    }

    /**
     * @param $storeInUrl
     * @param $disableStoreInUrl
     * @param $expectedResult
     * @dataProvider isUseStoreInUrlDataProvider
     */
    public function testIsUseStoreInUrl($storeInUrl, $disableStoreInUrl, $expectedResult)
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $configMock = $this->createMock(\Magento\Framework\App\Config\ReinitableConfigInterface::class);
        $appStateMock = $this->createMock(\Magento\Framework\App\State::class);

        $params = $this->modelParams;
        $params['context'] = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create(\Magento\Framework\Model\Context::class, ['appState' => $appStateMock]);

        $configMock->expects($this->any())
            ->method('getValue')
            ->with($this->stringContains(Store::XML_PATH_STORE_IN_URL))
            ->will($this->returnValue($storeInUrl));

        $params['config'] = $configMock;
        $model = $objectManager->create(\Magento\Store\Model\Store::class, $params);
        $model->setDisableStoreInUrl($disableStoreInUrl);
        $this->assertEquals($expectedResult, $model->isUseStoreInUrl());
    }

    /**
     * @see self::testIsUseStoreInUrl;
     * @return array
     */
    public function isUseStoreInUrlDataProvider()
    {
        return [
            [true, null, true],
            [false, null, false],
            [true, true, false],
            [true, false, true]
        ];
    }

    /**
     * @dataProvider isCurrentlySecureDataProvider
     *
     * @param bool $expected
     * @param array $serverValues
     * @magentoConfigFixture current_store web/secure/offloader_header X_FORWARDED_PROTO
     * @magentoConfigFixture current_store web/secure/base_url https://example.com:80
     */
    public function testIsCurrentlySecure($expected, $serverValues)
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var Store $model */
        $model = $objectManager->create(\Magento\Store\Model\Store::class);

        $request = $objectManager->get(\Magento\Framework\App\RequestInterface::class);
        $request->setServer(new Parameters(array_merge($_SERVER, $serverValues)));

        $this->assertEquals($expected, $model->isCurrentlySecure());
    }

    public function isCurrentlySecureDataProvider()
    {
        return [
            [true, ['HTTPS' => 'on']],
            [true, ['X_FORWARDED_PROTO' => 'https']],
            [true, ['HTTP_X_FORWARDED_PROTO' => 'https']],
            [true, ['HTTPS' => 'on', 'SERVER_PORT' => 80]],
            [false, ['SERVER_PORT' => 80]],
            [false, []],
        ];
    }

    /**
     * @magentoConfigFixture current_store web/secure/offloader_header SSL_OFFLOADED
     * @magentoConfigFixture current_store web/secure/base_url
     */
    public function testIsCurrentlySecureNoSecureBaseUrl()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var Store $model */
        $model = $objectManager->create(\Magento\Store\Model\Store::class);

        $server = $_SERVER;
        $_SERVER['SERVER_PORT'] = 80;

        $this->assertFalse($model->isCurrentlySecure());
        $_SERVER = $server;
    }
}

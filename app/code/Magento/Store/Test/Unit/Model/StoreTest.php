<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Store\Test\Unit\Model;

use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;

/**
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @SuppressWarnings(PHPMD.UnusedLocalVariable)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class StoreTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Store\Model\Store
     */
    protected $store;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManagerHelper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Framework\App\RequestInterface
     */
    protected $requestMock;

    /**
     * @var \Magento\Framework\Filesystem|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $filesystemMock;

    /**
     * @var \Magento\Framework\Url\ModifierInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $urlModifierMock;

    /**
     * @return void
     */
    protected function setUp()
    {
        $this->objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->requestMock = $this->createPartialMock(\Magento\Framework\App\Request\Http::class, [
            'getRequestString',
            'getModuleName',
            'setModuleName',
            'getActionName',
            'setActionName',
            'getParam',
            'getQueryValue',
            'getDistroBaseUrl',
            'isSecure',
            'getServer',
        ]);
        $this->filesystemMock = $this->getMockBuilder(\Magento\Framework\Filesystem::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->store = $this->objectManagerHelper->getObject(
            \Magento\Store\Model\Store::class,
            ['filesystem' => $this->filesystemMock]
        );

        $this->urlModifierMock = $this->createMock(\Magento\Framework\Url\ModifierInterface::class);
        $this->urlModifierMock->expects($this->any())
            ->method('execute')
            ->willReturnArgument(0);
    }

    /**
     * @dataProvider loadDataProvider
     *
     * @param string|int $key
     * @param string $field
     */
    public function testLoad($key, $field)
    {
        /** @var \Magento\Store\Model\ResourceModel\Store $resource */
        $resource = $this->createPartialMock(
            \Magento\Store\Model\ResourceModel\Store::class,
            ['load', 'getIdFieldName', '__wakeup']
        );
        $resource->expects($this->atLeastOnce())->method('load')
            ->with($this->isInstanceOf(\Magento\Store\Model\Store::class), $this->equalTo($key), $this->equalTo($field))
            ->will($this->returnSelf());
        $resource->expects($this->atLeastOnce())->method('getIdFieldName')->will($this->returnValue('store_id'));
        /** @var \Magento\Store\Model\Store $model */
        $model = $this->objectManagerHelper->getObject(\Magento\Store\Model\Store::class, ['resource' => $resource]);
        $model->load($key);
    }

    /**
     * @return array
     */
    public function loadDataProvider()
    {
        return [
            [1, null],
            ['default', 'code'],
        ];
    }

    /**
     * @return void
     */
    public function testSetWebsite()
    {
        $website = $this->createPartialMock(\Magento\Store\Model\Website::class, ['getId', '__wakeup']);
        $website->expects($this->atLeastOnce())->method('getId')->will($this->returnValue(2));
        /** @var \Magento\Store\Model\Store $model */
        $model = $this->objectManagerHelper->getObject(\Magento\Store\Model\Store::class);
        $model->setWebsite($website);
        $this->assertEquals(2, $model->getWebsiteId());
    }

    /**
     * @return void
     */
    public function testGetWebsite()
    {
        $websiteId = 2;
        $website = $this->createMock(\Magento\Store\Api\Data\WebsiteInterface::class);

        $websiteRepository = $this->getMockBuilder(\Magento\Store\Api\WebsiteRepositoryInterface::class)
            ->setMethods(['getById'])
            ->getMockForAbstractClass();
        $websiteRepository->expects($this->once())
            ->method('getById')
            ->with($websiteId)
            ->willReturn($website);

        /** @var \Magento\Store\Model\Store $model */
        $model = $this->objectManagerHelper->getObject(
            \Magento\Store\Model\Store::class,
            ['websiteRepository' => $websiteRepository,]
        );
        $model->setWebsiteId($websiteId);

        $this->assertEquals($website, $model->getWebsite());
    }

    /**
     * @return void
     */
    public function testGetWebsiteIfWebsiteIsNotExist()
    {
        $websiteRepository = $this->getMockBuilder(\Magento\Store\Api\WebsiteRepositoryInterface::class)
            ->setMethods(['getById'])
            ->getMockForAbstractClass();
        $websiteRepository->expects($this->never())
            ->method('getById');

        /** @var \Magento\Store\Model\Store $model */
        $model = $this->objectManagerHelper->getObject(
            \Magento\Store\Model\Store::class,
            ['websiteRepository' => $websiteRepository,]
        );
        $model->setWebsiteId(null);

        $this->assertFalse($model->getWebsite());
    }

    /**
     * @return void
     */
    public function testGetGroup()
    {
        $groupId = 2;
        $group = $this->createMock(\Magento\Store\Api\Data\GroupInterface::class);

        $groupRepository = $this->getMockBuilder(\Magento\Store\Api\GroupRepositoryInterface::class)
            ->setMethods(['get'])
            ->getMockForAbstractClass();
        $groupRepository->expects($this->once())
            ->method('get')
            ->with($groupId)
            ->willReturn($group);

        /** @var \Magento\Store\Model\Store $model */
        $model = $this->objectManagerHelper->getObject(
            \Magento\Store\Model\Store::class,
            ['groupRepository' => $groupRepository,]
        );
        $model->setGroupId($groupId);

        $this->assertEquals($group, $model->getGroup());
    }

    /**
     * @return void
     */
    public function testGetGroupIfGroupIsNotExist()
    {
        $groupRepository = $this->getMockBuilder(\Magento\Store\Api\GroupRepositoryInterface::class)
            ->setMethods(['getById'])
            ->getMockForAbstractClass();
        $groupRepository->expects($this->never())
            ->method('getById');

        /** @var \Magento\Store\Model\Store $model */
        $model = $this->objectManagerHelper->getObject(
            \Magento\Store\Model\Store::class,
            ['groupRepository' => $groupRepository,]
        );
        $model->setGroupId(null);

        $this->assertFalse($model->getGroup());
    }

    /**
     * @return void
     */
    public function testGetUrl()
    {
        $params = ['_scope_to_url' => true];
        $defaultStore = $this->createPartialMock(\Magento\Store\Model\Store::class, ['getId', '__wakeup']);
        $defaultStore->expects($this->atLeastOnce())->method('getId')->will($this->returnValue(5));

        $url = $this->getMockForAbstractClass(\Magento\Framework\UrlInterface::class);
        $url->expects($this->atLeastOnce())->method('setScope')->will($this->returnSelf());
        $url->expects($this->atLeastOnce())->method('getUrl')
            ->with($this->equalTo('test/route'), $this->equalTo($params))
            ->will($this->returnValue('http://test/url'));

        $storeManager = $this->getMockForAbstractClass(\Magento\Store\Model\StoreManagerInterface::class);
        $storeManager->expects($this->any())
            ->method('getStore')
            ->will($this->returnValue($defaultStore));

        /** @var \Magento\Store\Model\Store $model */
        $model = $this->objectManagerHelper->getObject(
            \Magento\Store\Model\Store::class,
            ['storeManager' => $storeManager, 'url' => $url]
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
     * @covers \Magento\Store\Model\Store::getConfig
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
        $configMock = $this->getMockForAbstractClass(\Magento\Framework\App\Config\ReinitableConfigInterface::class);
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
            \Magento\Store\Model\Store::class,
            [
                'config' => $configMock,
                'request' => $this->requestMock,
                'isCustomEntryPoint' => !$secure,
            ]
        );
        $model->setCode('scopeCode');

        $this->setUrlModifier($model);

        $this->assertEquals($expectedBaseUrl, $model->getBaseUrl($type, $secure));
    }

    /**
     * @return array
     */
    public function getBaseUrlDataProvider()
    {
        return [
            [
                \Magento\Framework\UrlInterface::URL_TYPE_WEB,
                false,
                'web/unsecure/base_url',
                'http://domain.com/web/unsecure/base_url/'
            ],
            [
                \Magento\Framework\UrlInterface::URL_TYPE_LINK,
                false,
                'web/unsecure/base_link_url',
                'http://domain.com/web/unsecure/base_link_url/index.php/'
            ],
            [
                \Magento\Framework\UrlInterface::URL_TYPE_DIRECT_LINK,
                false,
                'web/unsecure/base_link_url',
                'http://domain.com/web/unsecure/base_link_url/index.php/'
            ],
            [
                \Magento\Framework\UrlInterface::URL_TYPE_MEDIA,
                false,
                'web/unsecure/base_media_url',
                'http://domain.com/web/unsecure/base_media_url/'
            ],
            [
                \Magento\Framework\UrlInterface::URL_TYPE_STATIC,
                false,
                'web/unsecure/base_static_url',
                'http://domain.com/web/unsecure/base_static_url/'
            ],
            [
                \Magento\Framework\UrlInterface::URL_TYPE_MEDIA,
                false,
                'web/unsecure/base_url',
                'http://domain.com/web/unsecure/base_url/'
            ],
            [
                \Magento\Framework\UrlInterface::URL_TYPE_STATIC,
                false,
                'web/unsecure/base_url',
                'http://domain.com/web/unsecure/base_url/'
            ],
            [
                \Magento\Framework\UrlInterface::URL_TYPE_WEB,
                true,
                'web/secure/base_url',
                'http://distro.com/web/secure/base_url/'
            ],
        ];
    }

    /**
     * @return void
     */
    public function testGetBaseUrlEntryPoint()
    {
        $expectedPath = 'web/unsecure/base_link_url';
        $expectedBaseUrl = 'http://domain.com/web/unsecure/base_link_url/test_script.php/';
        /** @var \Magento\Framework\App\Config\ReinitableConfigInterface $configMock */
        $configMock = $this->getMockForAbstractClass(\Magento\Framework\App\Config\ReinitableConfigInterface::class);
        $configMock->expects($this->atLeastOnce())
            ->method('getValue')
            ->will($this->returnCallback(
                function ($path, $scope, $scopeCode) use ($expectedPath) {
                    return $expectedPath == $path ? 'http://domain.com/' . $path . '/' : null;
                }
            ));
        /** @var \Magento\Store\Model\Store $model */
        $model = $this->objectManagerHelper->getObject(
            \Magento\Store\Model\Store::class,
            [
                'config' => $configMock,
                'isCustomEntryPoint' => false,
            ]
        );
        $model->setCode('scopeCode');

        $this->setUrlModifier($model);

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
            \Magento\Store\Model\Store::class
        );
        $model->getBaseUrl('unexpected url type');
    }

    /**
     * @dataProvider getCurrentUrlDataProvider
     *
     * @param boolean $secure
     * @param string $url
     * @param string $expected
     * @param bool|string $fromStore
     */
    public function testGetCurrentUrl($secure, $url, $expected, $fromStore)
    {
        $defaultStore = $this->createPartialMock(Store::class, [
            'getId',
            'isCurrentlySecure',
            '__wakeup'
        ]);
        $defaultStore->expects($this->atLeastOnce())->method('getId')->will($this->returnValue(5));
        $defaultStore->expects($this->atLeastOnce())->method('isCurrentlySecure')->will($this->returnValue($secure));

        $sidResolver = $this->getMockForAbstractClass(\Magento\Framework\Session\SidResolverInterface::class);
        $sidResolver->expects($this->any())->method('getSessionIdQueryParam')->will($this->returnValue('SID'));

        $config = $this->getMockForAbstractClass(\Magento\Framework\App\Config\ReinitableConfigInterface::class);

        $requestString = preg_replace(
            '/http(s?)\:\/\/[a-z0-9\-]+\//i',
            '',
            $url
        );
        $this->requestMock
            ->expects($this->atLeastOnce())
            ->method('getRequestString')
            ->willReturn($requestString);
        $this->requestMock->expects($this->atLeastOnce())->method('getQueryValue')->will($this->returnValue([
            'SID' => 'sid'
        ]));

        $urlMock = $this->getMockForAbstractClass(\Magento\Framework\UrlInterface::class);
        $urlMock
            ->expects($this->atLeastOnce())
            ->method('setScope')
            ->will($this->returnSelf());
        $urlMock->expects($this->any())
            ->method('getUrl')
            ->will($this->returnValue(str_replace($requestString, '', $url)));
        $urlMock
            ->expects($this->atLeastOnce())
            ->method('escape')
            ->willReturnArgument(0);

        $storeManager = $this->getMockForAbstractClass(\Magento\Store\Model\StoreManagerInterface::class);
        $storeManager->expects($this->any())
            ->method('getStore')
            ->will($this->returnValue($defaultStore));

        /** @var \Magento\Store\Model\Store $model */
        $model = $this->objectManagerHelper->getObject(
            \Magento\Store\Model\Store::class,
            ['storeManager' => $storeManager, 'url' => $urlMock, 'request' => $this->requestMock, 'config' => $config]
        );
        $model->setStoreId(2);
        $model->setCode('scope_code');

        $this->assertEquals($expected, $model->getCurrentUrl($fromStore));
    }

    /**
     * @return array
     */
    public function getCurrentUrlDataProvider()
    {
        return [
            [
                true,
                'http://test/url',
                'http://test/url?SID=sid&___store=scope_code',
                false
            ],
            [
                true,
                'http://test/url?SID=sid1&___store=scope',
                'http://test/url?SID=sid&___store=scope_code',
                false
            ],
            [
                false,
                'https://test/url',
                'https://test/url?SID=sid&___store=scope_code',
                false
            ],
            [
                true,
                'http://test/u/u.2?___store=scope_code',
                'http://test/u/u.2?'
                . '___store=scope_code&SID=sid&___from_store=old-store',
                'old-store'
            ]
        ];
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
        $config = $this->getMockForAbstractClass(\Magento\Framework\App\Config\ReinitableConfigInterface::class);
        $config->expects($this->any())
            ->method('getValue')
            ->will($this->returnValueMap([
                ['catalog/price/scope', ScopeInterface::SCOPE_STORE, 'scope_code', $priceScope],
                [
                    \Magento\Directory\Model\Currency::XML_PATH_CURRENCY_BASE,
                    ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                    null,
                    'USD'
                ],
                [
                    \Magento\Directory\Model\Currency::XML_PATH_CURRENCY_BASE,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    'scope_code',
                    'UAH'
                ],
            ]));

        $currency = $this->createMock(\Magento\Directory\Model\Currency::class);
        $currency->expects($this->any())->method('load')->with($currencyCode)->will($this->returnSelf());

        $currencyFactory = $this->createPartialMock(\Magento\Directory\Model\CurrencyFactory::class, ['create']);
        $currencyFactory->expects($this->any())->method('create')->will($this->returnValue($currency));

        $appState = $this->createPartialMock(\Magento\Framework\App\State::class, ['isInstalled']);
        $appState->expects($this->any())->method('isInstalled')->will($this->returnValue(true));
        /** @var \Magento\Store\Model\Store $model */
        $model = $this->objectManagerHelper->getObject(
            \Magento\Store\Model\Store::class,
            ['currencyFactory' => $currencyFactory, 'config' => $config, 'appState' => $appState]
        );
        $model->setCode('scope_code');
        $this->assertEquals($currency, $model->getBaseCurrency());
    }

    /**
     * @return array
     */
    public function getBaseCurrencyDataProvider()
    {
        return [
            [0, 'USD'],
            [1, 'UAH'],
        ];
    }

    /**
     * @return void
     */
    public function testGetAllowedCurrencies()
    {
        $currencyPath = 'cur/ren/cy/path';
        $expectedResult = ['EUR', 'USD'];

        $configMock = $this->getMockForAbstractClass(
            \Magento\Framework\App\Config\ReinitableConfigInterface::class,
            [],
            '',
            false
        );
        $configMock->expects($this->once())
            ->method('getValue')
            ->with($currencyPath, 'store', null)
            ->will($this->returnValue('EUR,USD'));

        /** @var \Magento\Store\Model\Store $model */
        $model = $this->objectManagerHelper->getObject(
            \Magento\Store\Model\Store::class,
            ['config' => $configMock, 'currencyInstalled' => $currencyPath,]
        );

        $this->assertEquals($expectedResult, $model->getAllowedCurrencies());
    }

    /**
     * @dataProvider isCurrentlySecureDataProvider
     *
     * @param bool $expected
     * @param array $value
     * @param bool $requestSecure
     * @param bool $useSecureInFrontend
     * @param string|null $secureBaseUrl
     */
    public function testIsCurrentlySecure(
        $expected,
        $value,
        $requestSecure = false,
        $useSecureInFrontend = true,
        $secureBaseUrl = 'https://example.com:443'
    ) {
        /* @var ReinitableConfigInterface|PHPUnit_Framework_MockObject_MockObject $configMock */
        $configMock = $this->getMockForAbstractClass(\Magento\Framework\App\Config\ReinitableConfigInterface::class);
        $configMock->expects($this->any())
            ->method('getValue')
            ->will($this->returnValueMap([
                        [
                            Store::XML_PATH_SECURE_BASE_URL,
                            ScopeInterface::SCOPE_STORE,
                            null,
                            $secureBaseUrl
                        ],
                        [
                            Store::XML_PATH_SECURE_IN_FRONTEND,
                            ScopeInterface::SCOPE_STORE,
                            null,
                            $useSecureInFrontend
                        ]
                    ]));

        $this->requestMock->expects($this->any())
            ->method('isSecure')
            ->willReturn($requestSecure);

        $this->requestMock->expects($this->any())
            ->method('getServer')
            ->with($this->equalTo('SERVER_PORT'))
            ->willReturn($value);

        /** @var \Magento\Store\Model\Store $model */
        $model = $this->objectManagerHelper->getObject(
            \Magento\Store\Model\Store::class,
            ['config' => $configMock, 'request' => $this->requestMock]
        );

        if ($expected) {
            $this->assertTrue($model->isCurrentlySecure(), "Was expecting this test to show as secure, but it wasn't");
        } else {
            $this->assertFalse($model->isCurrentlySecure(), "Was expecting this test to show as not secure!");
        }
    }

    /**
     * @return array
     */
    public function isCurrentlySecureDataProvider()
    {
        return [
            'secure request, no server setting' => [true, [], true],
            'unsecure request, using registered port' => [true, 443],
            'unsecure request, no secure base url registered' => [false, 443, false, true, null],
            'unsecure request, not using registered port' => [false, 80],
            'unsecure request, using registered port, not using secure in frontend' => [false, 443, false, false],
            'unsecure request, no secure base url registered, not using secure in frontend' =>
                [false, 443, false, false, null],
            'unsecure request, not using registered port, not using secure in frontend' => [false, 80, false, false],
        ];
    }

    /**
     * @covers \Magento\Store\Model\Store::getBaseMediaDir
     */
    public function testGetBaseMediaDir()
    {
        $expectedResult = 'pub/media';
        $this->filesystemMock->expects($this->once())
            ->method('getUri')
            ->with(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)
            ->willReturn($expectedResult);
        $this->assertEquals($expectedResult, $this->store->getBaseMediaDir());
    }

    /**
     * @covers \Magento\Store\Model\Store::getBaseStaticDir
     */
    public function testGetBaseStaticDir()
    {
        $expectedResult = 'pub/static';
        $this->filesystemMock->expects($this->once())
            ->method('getUri')
            ->with(\Magento\Framework\App\Filesystem\DirectoryList::STATIC_VIEW)
            ->willReturn($expectedResult);
        $this->assertEquals($expectedResult, $this->store->getBaseStaticDir());
    }

    /**
     * @return void
     */
    public function testGetScopeType()
    {
        $this->assertEquals(ScopeInterface::SCOPE_STORE, $this->store->getScopeType());
    }

    /**
     * @return void
     */
    public function testGetScopeTypeName()
    {
        $this->assertEquals('Store View', $this->store->getScopeTypeName());
    }

    /**
     * @param \Magento\Store\Model\Store $model
     */
    private function setUrlModifier(\Magento\Store\Model\Store $model)
    {
        $property = (new \ReflectionClass(get_class($model)))
            ->getProperty('urlModifier');

        $property->setAccessible(true);
        $property->setValue($model, $this->urlModifierMock);
    }
}

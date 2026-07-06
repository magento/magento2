<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Store\Test\Unit\Model;

use Magento\Directory\Model\Currency;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\State;
use Magento\Framework\Filesystem;
use Magento\Framework\Session\Generic as SessionGeneric;
use Magento\Framework\Session\SessionManager;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\Session\SessionStartChecker;
use Magento\Framework\Session\SidResolverInterface;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Url\ModifierInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Api\Data\GroupInterface;
use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Store\Model\GroupRepository;
use Magento\Store\Model\ResourceModel\Store as StoreResourceModel;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Website;
use Magento\Store\Model\WebsiteRepository;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @SuppressWarnings(PHPMD.UnusedLocalVariable)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class StoreTest extends TestCase
{
    use MockCreationTrait;
    /**
     * @var Store
     */
    protected $store;

    /**
     * @var ObjectManager
     */
    protected $objectManagerHelper;

    /**
     * @var MockObject|RequestInterface
     */
    protected $requestMock;

    /**
     * @var Filesystem|MockObject
     */
    protected $filesystemMock;

    /**
     * @var ReinitableConfigInterface|MockObject
     */
    private $configMock;

    /**
     * @var SessionManagerInterface|MockObject
     */
    private $sessionMock;

    /**
     * @var ModifierInterface|MockObject
     */
    private $urlModifierMock;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManager($this);
        $this->requestMock = $this->createPartialMock(Http::class, [
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

        $this->filesystemMock = $this->createMock(Filesystem::class);
        $this->configMock = $this->createMock(ReinitableConfigInterface::class);
        $this->sessionMock = $this->createPartialMockWithReflection(
            SessionManagerInterface::class,
            [
                'start', 'writeClose', 'isSessionExists', 'getSessionId', 'getName', 'setName',
                'destroy', 'clearStorage', 'getCookieDomain', 'getCookiePath', 'getCookieLifetime',
                'setSessionId', 'regenerateId', 'expireSessionCookie', 'getSessionIdForHost',
                'isValidForHost', 'isValidForPath', 'getCurrencyCode'
            ]
        );
        
        $this->store = $this->objectManagerHelper->getObject(
            Store::class,
            [
                'filesystem' => $this->filesystemMock,
                'config' => $this->configMock,
                'session' => $this->sessionMock,
            ]
        );

        $this->urlModifierMock = $this->createMock(ModifierInterface::class);
        $this->urlModifierMock->expects($this->any())
            ->method('execute')
            ->willReturnArgument(0);
    }

    /**
     * @param string|int $key
     * @param string $field
     */
    #[DataProvider('loadDataProvider')]
    public function testLoad($key, $field)
    {
        /** @var StoreResourceModel $resource */
        $resource = $this->createPartialMock(
            StoreResourceModel::class,
            ['load', 'getIdFieldName', '__wakeup']
        );
        $resource->expects($this->atLeastOnce())->method('load')
            ->with($this->isInstanceOf(Store::class), $this->equalTo($key), $this->equalTo($field))
            ->willReturnSelf();
        $resource->expects($this->atLeastOnce())->method('getIdFieldName')->willReturn('store_id');
        /** @var Store $model */
        $model = $this->objectManagerHelper->getObject(Store::class, ['resource' => $resource]);
        $model->load($key);
    }

    /**
     * @return array
     */
    public static function loadDataProvider()
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
        $website = $this->createPartialMock(Website::class, ['getId', '__wakeup']);
        $website->expects($this->atLeastOnce())->method('getId')->willReturn(2);
        /** @var Store $model */
        $model = $this->objectManagerHelper->getObject(Store::class);
        $model->setWebsite($website);
        $this->assertEquals(2, $model->getWebsiteId());
    }

    /**
     * @return void
     */
    public function testGetWebsite()
    {
        $websiteId = 2;
        $website = $this->createMock(WebsiteInterface::class);

        $websiteRepository = $this->createPartialMock(
            WebsiteRepository::class,
            ['getById']
        );
        $websiteRepository->expects($this->once())
            ->method('getById')
            ->with($websiteId)
            ->willReturn($website);

        /** @var Store $model */
        $model = $this->objectManagerHelper->getObject(
            Store::class,
            ['websiteRepository' => $websiteRepository]
        );
        $model->setWebsiteId($websiteId);

        $this->assertEquals($website, $model->getWebsite());
    }

    /**
     * @return void
     */
    public function testGetWebsiteIfWebsiteIsNotExist()
    {
        $websiteRepository = $this->createPartialMock(
            WebsiteRepository::class,
            ['getById']
        );
        $websiteRepository->expects($this->never())
            ->method('getById');

        /** @var Store $model */
        $model = $this->objectManagerHelper->getObject(
            Store::class,
            ['websiteRepository' => $websiteRepository]
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
        $group = $this->createMock(GroupInterface::class);

        $groupRepository = $this->createPartialMock(
            GroupRepository::class,
            ['get']
        );
        $groupRepository->expects($this->once())
            ->method('get')
            ->with($groupId)
            ->willReturn($group);

        /** @var Store $model */
        $model = $this->objectManagerHelper->getObject(
            Store::class,
            ['groupRepository' => $groupRepository]
        );
        $model->setGroupId($groupId);

        $this->assertEquals($group, $model->getGroup());
    }

    /**
     * @return void
     */
    public function testGetGroupIfGroupIsNotExist()
    {
        $groupRepository = $this->createMock(GroupRepository::class);

        /** @var Store $model */
        $model = $this->objectManagerHelper->getObject(
            Store::class,
            ['groupRepository' => $groupRepository]
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
        $defaultStore = $this->createPartialMock(Store::class, ['getId', '__wakeup']);
        $defaultStore->expects($this->atLeastOnce())->method('getId')->willReturn(5);

        $url = $this->createMock(UrlInterface::class);
        $url->expects($this->atLeastOnce())->method('setScope')->willReturnSelf();
        $url->expects($this->atLeastOnce())->method('getUrl')
            ->with('test/route', $params)
            ->willReturn('http://test/url');

        $storeManager = $this->createMock(StoreManagerInterface::class);
        $storeManager->expects($this->any())
            ->method('getStore')
            ->willReturn($defaultStore);

        /** @var Store $model */
        $model = $this->objectManagerHelper->getObject(
            Store::class,
            ['storeManager' => $storeManager, 'url' => $url]
        );
        $model->setStoreId(2);
        $this->assertEquals('http://test/url', $model->getUrl('test/route'));
    }

    /**
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
    #[DataProvider('getBaseUrlDataProvider')]
    public function testGetBaseUrl($type, $secure, $expectedPath, $expectedBaseUrl)
    {
        $this->requestMock->expects($this->any())
            ->method('getDistroBaseUrl')
            ->willReturn('http://distro.com/');

        /** @var \Magento\Framework\App\Config\ReinitableConfigInterface $configMock */
        $configMock = $this->createMock(ReinitableConfigInterface::class);
        $configMock->expects($this->atLeastOnce())
            ->method('getValue')
            ->willReturnCallback(
                function ($path, $scope, $scopeCode) use ($secure, $expectedPath) {
                    $url = $secure ? '{{base_url}}' : 'http://domain.com/';
                    return $expectedPath == $path ? $url . $path . '/' : null;
                }
            );
        /** @var Store $model */
        $model = $this->objectManagerHelper->getObject(
            Store::class,
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
    public static function getBaseUrlDataProvider()
    {
        return [
            [
                UrlInterface::URL_TYPE_WEB,
                false,
                'web/unsecure/base_url',
                'http://domain.com/web/unsecure/base_url/'
            ],
            [
                UrlInterface::URL_TYPE_LINK,
                false,
                'web/unsecure/base_link_url',
                'http://domain.com/web/unsecure/base_link_url/index.php/'
            ],
            [
                UrlInterface::URL_TYPE_DIRECT_LINK,
                false,
                'web/unsecure/base_link_url',
                'http://domain.com/web/unsecure/base_link_url/index.php/'
            ],
            [
                UrlInterface::URL_TYPE_MEDIA,
                false,
                'web/unsecure/base_media_url',
                'http://domain.com/web/unsecure/base_media_url/'
            ],
            [
                UrlInterface::URL_TYPE_STATIC,
                false,
                'web/unsecure/base_static_url',
                'http://domain.com/web/unsecure/base_static_url/'
            ],
            [
                UrlInterface::URL_TYPE_MEDIA,
                false,
                'web/unsecure/base_url',
                'http://domain.com/web/unsecure/base_url/'
            ],
            [
                UrlInterface::URL_TYPE_STATIC,
                false,
                'web/unsecure/base_url',
                'http://domain.com/web/unsecure/base_url/'
            ],
            [
                UrlInterface::URL_TYPE_WEB,
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
        $configMock = $this->createMock(ReinitableConfigInterface::class);
        $configMock->expects($this->atLeastOnce())
            ->method('getValue')
            ->willReturnCallback(function ($path, $scope, $scopeCode) use ($expectedPath) {
                return $expectedPath == $path ? 'http://domain.com/' . $path . '/' : null;
            });
        $this->requestMock->expects($this->once())
            ->method('getServer')
            ->with('SCRIPT_FILENAME')
            ->willReturn('test_script.php');

        /** @var Store $model */
        $model = $this->objectManagerHelper->getObject(
            Store::class,
            [
                'config' => $configMock,
                'isCustomEntryPoint' => false,
                'request' => $this->requestMock
            ]
        );
        $model->setCode('scopeCode');

        $this->setUrlModifier($model);

        $this->assertEquals(
            $expectedBaseUrl,
            $model->getBaseUrl(UrlInterface::URL_TYPE_LINK, false)
        );
    }

    public function testGetBaseUrlWrongType()
    {
        $this->expectException(\InvalidArgumentException::class);
        /** @var Store $model */
        $model = $this->objectManagerHelper->getObject(
            Store::class
        );
        $model->getBaseUrl('unexpected url type');
    }

    /**
     * @param boolean $secure
     * @param string $url
     * @param string $expected
     * @param bool|string $fromStore
     */
    #[DataProvider('getCurrentUrlDataProvider')]
    public function testGetCurrentUrl($secure, $url, $expected, $fromStore)
    {
        $defaultStore = $this->createPartialMock(Store::class, [
            'getId',
            'isCurrentlySecure',
            '__wakeup'
        ]);
        $defaultStore->expects($this->atLeastOnce())->method('getId')->willReturn(5);
        $defaultStore->expects($this->atLeastOnce())->method('isCurrentlySecure')->willReturn($secure);

        $sidResolver = $this->createMock(SidResolverInterface::class);
        $sidResolver->expects($this->any())->method('getSessionIdQueryParam')->willReturn('SID');

        $config = $this->createMock(ReinitableConfigInterface::class);

        $requestString = preg_replace(
            '/http(s?)\:\/\/[a-z0-9\-]+\//i',
            '',
            $url
        );
        $this->requestMock
            ->expects($this->atLeastOnce())
            ->method('getRequestString')
            ->willReturn($requestString);
        $this->requestMock->expects($this->atLeastOnce())->method('getQueryValue')->willReturn([
            'SID' => 'sid'
        ]);

        $urlMock = $this->createMock(UrlInterface::class);
        $urlMock
            ->expects($this->atLeastOnce())
            ->method('setScope')->willReturnSelf();
        $urlMock->expects($this->any())
            ->method('getUrl')
            ->willReturn(str_replace($requestString, '', $url));
        $urlMock
            ->expects($this->atLeastOnce())
            ->method('escape')
            ->willReturnArgument(0);

        $storeManager = $this->createMock(StoreManagerInterface::class);
        $storeManager->expects($this->any())
            ->method('getStore')
            ->willReturn($defaultStore);

        /** @var Store $model */
        $model = $this->objectManagerHelper->getObject(
            Store::class,
            ['storeManager' => $storeManager, 'url' => $urlMock, 'request' => $this->requestMock, 'config' => $config]
        );
        $model->setStoreId(2);
        $model->setCode('scope_code');

        $this->assertEquals($expected, $model->getCurrentUrl($fromStore));
    }

    /**
     * @return array
     */
    public static function getCurrentUrlDataProvider()
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
     * @param int $priceScope
     * @param string $currencyCode
     */
    #[DataProvider('getBaseCurrencyDataProvider')]
    public function testGetBaseCurrency($priceScope, $currencyCode)
    {
        /** @var \Magento\Framework\App\Config\ReinitableConfigInterface $config */
        $config = $this->createMock(ReinitableConfigInterface::class);
        $config->expects($this->any())
            ->method('getValue')
            ->willReturnMap([
                ['catalog/price/scope', ScopeInterface::SCOPE_STORE, 'scope_code', $priceScope],
                [
                    Currency::XML_PATH_CURRENCY_BASE,
                    ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                    null,
                    'USD'
                ],
                [
                    Currency::XML_PATH_CURRENCY_BASE,
                    ScopeInterface::SCOPE_STORE,
                    'scope_code',
                    'UAH'
                ],
            ]);

        $currency = $this->createMock(Currency::class);
        $currency->expects($this->any())->method('load')->with($currencyCode)->willReturnSelf();

        $currencyFactory = $this->createPartialMock(CurrencyFactory::class, ['create']);
        $currencyFactory->expects($this->any())->method('create')->willReturn($currency);

        $appState = $this->createPartialMockWithReflection(State::class, ['isInstalled']);
        $appState->expects($this->any())->method('isInstalled')->willReturn(true);
        /** @var Store $model */
        $model = $this->objectManagerHelper->getObject(
            Store::class,
            ['currencyFactory' => $currencyFactory, 'config' => $config, 'appState' => $appState]
        );
        $model->setCode('scope_code');
        $this->assertEquals($currency, $model->getBaseCurrency());
    }

    /**
     * @return array
     */
    public static function getBaseCurrencyDataProvider()
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

        $configMock = $this->createMock(ReinitableConfigInterface::class);
        $configMock->expects($this->once())
            ->method('getValue')
            ->with($currencyPath, 'store', null)
            ->willReturn('EUR,USD');

        /** @var Store $model */
        $model = $this->objectManagerHelper->getObject(
            Store::class,
            ['config' => $configMock, 'currencyInstalled' => $currencyPath]
        );

        $this->assertEquals($expectedResult, $model->getAllowedCurrencies());
    }

    /**
     * @param bool $expected
     * @param array|int|null $value
     * @param bool $requestSecure
     * @param bool $useSecureInFrontend
     * @param string|null $secureBaseUrl
     */
    #[DataProvider('isCurrentlySecureDataProvider')]
    public function testIsCurrentlySecure(
        bool        $expected,
        array|int|null   $value,
        bool        $requestSecure = false,
        bool        $useSecureInFrontend = true,
        ?string     $secureBaseUrl = 'https://example.com:443'
    ) {
        /* @var ReinitableConfigInterface|MockObject $configMock */
        $configMock = $this->createMock(ReinitableConfigInterface::class);
        $configMock->expects($this->any())
            ->method('getValue')
            ->willReturnMap([
                [
                    Store::XML_PATH_SECURE_BASE_URL,
                    ScopeInterface::SCOPE_STORE,
                    2,
                    $secureBaseUrl
                ],
                [
                    Store::XML_PATH_SECURE_IN_FRONTEND,
                    ScopeInterface::SCOPE_STORE,
                    2,
                    $useSecureInFrontend
                ]
            ]);

        $this->requestMock->expects($this->any())
            ->method('isSecure')
            ->willReturn($requestSecure);

        $this->requestMock->expects($this->any())
            ->method('getServer')
            ->with('SERVER_PORT')
            ->willReturn($value);

        /** @var Store $model */
        $model = $this->objectManagerHelper->getObject(
            Store::class,
            ['config' => $configMock, 'request' => $this->requestMock]
        );

        $model->setStoreId(2);

        if ($expected) {
            $this->assertTrue($model->isCurrentlySecure(), "Was expecting this test to show as secure, but it wasn't");
        } else {
            $this->assertFalse($model->isCurrentlySecure(), "Was expecting this test to show as not secure!");
        }
    }

    /**
     * @return array
     */
    public static function isCurrentlySecureDataProvider()
    {
        return [
            'secure request, no server setting' => [true, [], true],
            'unsecure request, using registered port' => [true, 443],
            'unsecure request, no secure base url registered' => [false, 443, false, true, null],
            'unsecure request, not using registered port' => [false, 80],
            'unsecure request, using registered port, not using secure in frontend' => [false, 443, false, false],
            'unsecure request, no secure base url, not using secure in frontend' => [false, 443, false, false, null],
            'unsecure request, not using registered port, not using secure in frontend' => [false, 80, false, false],
            'unsecure request, no server setting' => [false, null, false],
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
            ->with(DirectoryList::MEDIA)
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
            ->with(DirectoryList::STATIC_VIEW)
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

    public function testGetCacheTags()
    {
        $this->assertEquals([Store::CACHE_TAG], $this->store->getCacheTags());
    }

    /**
     * @param array $availableCodes
     * @param string $currencyCode
     * @param string $defaultCode
     * @param string $expectedCode
     * @return void
     */
    #[DataProvider('currencyCodeDataProvider')]
    public function testGetCurrentCurrencyCode(
        array $availableCodes,
        string $currencyCode,
        string $defaultCode,
        string $expectedCode
    ): void {
        $this->store->setData('available_currency_codes', $availableCodes);
        $this->sessionMock->method('getCurrencyCode')
            ->willReturn($currencyCode);
        $this->configMock->method('getValue')
            ->with(Currency::XML_PATH_CURRENCY_DEFAULT)
            ->willReturn($defaultCode);

        $code = $this->store->getCurrentCurrencyCode();
        $this->assertEquals($expectedCode, $code);
    }

    /**
     * @return array
     */
    public static function currencyCodeDataProvider(): array
    {
        return [
            [
                [
                    'USD',
                ],
                'USD',
                'USD',
                'USD',
            ],
            [
                [
                    'USD',
                    'EUR',
                ],
                'EUR',
                'USD',
                'EUR',
            ],
            [
                [
                    'EUR',
                    'USD',
                ],
                'GBP',
                'USD',
                'USD',
            ],
            [
                [
                    'USD',
                ],
                'GBP',
                'EUR',
                'USD',
            ],
            [
                [],
                'GBP',
                'EUR',
                'EUR',
            ],
        ];
    }

    /**
     * @param Store $model
     */
    private function setUrlModifier(Store $model)
    {
        $property = (new \ReflectionClass(get_class($model)))
            ->getProperty('urlModifier');

        $property->setValue($model, $this->urlModifierMock);
    }
}

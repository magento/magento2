<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Store\Test\Unit\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Store\Model\Store;

/**
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @SuppressWarnings(PHPMD.UnusedLocalVariable)
 */
class StoreTest extends \PHPUnit_Framework_TestCase
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

    protected function setUp()
    {
        $this->objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->requestMock = $this->getMock('Magento\Framework\App\Request\Http', [
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
        ], [], '', false);
        $this->filesystemMock = $this->getMockBuilder('Magento\Framework\Filesystem')
            ->disableOriginalConstructor()
            ->getMock();
        $this->store = $this->objectManagerHelper->getObject(
            'Magento\Store\Model\Store',
            ['filesystem' => $this->filesystemMock]
        );

        $this->urlModifierMock = $this->getMock('Magento\Framework\Url\ModifierInterface');
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
        $resource = $this->getMock(
            '\Magento\Store\Model\ResourceModel\Store',
            ['load', 'getIdFieldName', '__wakeup'],
            [],
            '',
            false
        );
        $resource->expects($this->atLeastOnce())->method('load')
            ->with($this->isInstanceOf('\Magento\Store\Model\Store'), $this->equalTo($key), $this->equalTo($field))
            ->will($this->returnSelf());
        $resource->expects($this->atLeastOnce())->method('getIdFieldName')->will($this->returnValue('store_id'));
        /** @var \Magento\Store\Model\Store $model */
        $model = $this->objectManagerHelper->getObject('Magento\Store\Model\Store', ['resource' => $resource]);
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

    public function testSetWebsite()
    {
        $website = $this->getMock('\Magento\Store\Model\Website', ['getId', '__wakeup'], [], '', false);
        $website->expects($this->atLeastOnce())->method('getId')->will($this->returnValue(2));
        /** @var \Magento\Store\Model\Store $model */
        $model = $this->objectManagerHelper->getObject('Magento\Store\Model\Store');
        $model->setWebsite($website);
        $this->assertEquals(2, $model->getWebsiteId());
    }

    public function testGetWebsite()
    {
        $websiteId = 2;
        $website = $this->getMock('Magento\Store\Api\Data\WebsiteInterface');

        $websiteRepository = $this->getMock('Magento\Store\Api\WebsiteRepositoryInterface');
        $websiteRepository->expects($this->once())
            ->method('getById')
            ->with($websiteId)
            ->willReturn($website);

        /** @var \Magento\Store\Model\Store $model */
        $model = $this->objectManagerHelper->getObject('Magento\Store\Model\Store', [
            'websiteRepository' => $websiteRepository,
        ]);
        $model->setWebsiteId($websiteId);

        $this->assertEquals($website, $model->getWebsite());
    }

    public function testGetWebsiteIfWebsiteIsNotExist()
    {
        $websiteRepository = $this->getMock('Magento\Store\Api\WebsiteRepositoryInterface');
        $websiteRepository->expects($this->never())
            ->method('getById');

        /** @var \Magento\Store\Model\Store $model */
        $model = $this->objectManagerHelper->getObject('Magento\Store\Model\Store', [
            'websiteRepository' => $websiteRepository,
        ]);
        $model->setWebsiteId(null);

        $this->assertFalse($model->getWebsite());
    }

    public function testGetGroup()
    {
        $groupId = 2;
        $group = $this->getMock('Magento\Store\Api\Data\GroupInterface');

        $groupRepository = $this->getMock('Magento\Store\Api\GroupRepositoryInterface');
        $groupRepository->expects($this->once())
            ->method('get')
            ->with($groupId)
            ->willReturn($group);

        /** @var \Magento\Store\Model\Store $model */
        $model = $this->objectManagerHelper->getObject('Magento\Store\Model\Store', [
            'groupRepository' => $groupRepository,
        ]);
        $model->setGroupId($groupId);

        $this->assertEquals($group, $model->getGroup());
    }

    public function testGetGroupIfGroupIsNotExist()
    {
        $groupRepository = $this->getMock('Magento\Store\Api\GroupRepositoryInterface');
        $groupRepository->expects($this->never())
            ->method('getById');

        /** @var \Magento\Store\Model\Store $model */
        $model = $this->objectManagerHelper->getObject('Magento\Store\Model\Store', [
            'groupRepository' => $groupRepository,
        ]);
        $model->setGroupId(null);

        $this->assertFalse($model->getGroup());
    }

    public function testGetUrl()
    {
        $params = ['_scope_to_url' => true];
        $defaultStore = $this->getMock('\Magento\Store\Model\Store', ['getId', '__wakeup'], [], '', false);
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
        $defaultStore = $this->getMock('\Magento\Store\Model\Store', [
            'getId',
            'isCurrentlySecure',
            '__wakeup'
        ], [], '', false);
        $defaultStore->expects($this->atLeastOnce())->method('getId')->will($this->returnValue(5));
        $defaultStore->expects($this->atLeastOnce())->method('isCurrentlySecure')->will($this->returnValue($secure));

        $sidResolver = $this->getMockForAbstractClass('\Magento\Framework\Session\SidResolverInterface');
        $sidResolver->expects($this->any())->method('getSessionIdQueryParam')->will($this->returnValue('SID'));

        $config = $this->getMockForAbstractClass('\Magento\Framework\App\Config\ReinitableConfigInterface');


        $this->requestMock->expects($this->atLeastOnce())->method('getRequestString')->will($this->returnValue(''));
        $this->requestMock->expects($this->atLeastOnce())->method('getQueryValue')->will($this->returnValue([
            'SID' => 'sid'
        ]));


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
        return [
            [true, 'http://test/url', 'http://test/url?SID=sid&amp;___store=scope_code'],
            [true, 'http://test/url?SID=sid1&___store=scope', 'http://test/url?SID=sid&amp;___store=scope_code'],
            [false, 'https://test/url', 'https://test/url?SID=sid&amp;___store=scope_code']
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
        $config = $this->getMockForAbstractClass('\Magento\Framework\App\Config\ReinitableConfigInterface');
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

        $currency = $this->getMock('\Magento\Directory\Model\Currency', [], [], '', false);
        $currency->expects($this->any())->method('load')->with($currencyCode)->will($this->returnSelf());

        $currencyFactory = $this->getMock(
            '\Magento\Directory\Model\CurrencyFactory',
            ['create'],
            [],
            '',
            false
        );
        $currencyFactory->expects($this->any())->method('create')->will($this->returnValue($currency));

        $appState = $this->getMock('\Magento\Framework\App\State', [], [], '', false);
        $appState->expects($this->any())->method('isInstalled')->will($this->returnValue(true));
        /** @var \Magento\Store\Model\Store $model */
        $model = $this->objectManagerHelper->getObject('Magento\Store\Model\Store',
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

    public function testGetAllowedCurrencies()
    {
        $currencyPath = 'cur/ren/cy/path';
        $expectedResult = ['EUR', 'USD'];

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
        $model = $this->objectManagerHelper->getObject('Magento\Store\Model\Store', [
            'config' => $configMock,
            'currencyInstalled' => $currencyPath,
        ]);

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
        $configMock = $this->getMockForAbstractClass('\Magento\Framework\App\Config\ReinitableConfigInterface');
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
            'Magento\Store\Model\Store',
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

    public function testGetScopeType()
    {
        $this->assertEquals(ScopeInterface::SCOPE_STORE, $this->store->getScopeType());
    }

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

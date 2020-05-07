<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\App\Test\Unit\Route;

use Magento\Framework\App\AreaList;
use Magento\Framework\App\Route\Config;
use Magento\Framework\App\Route\Config\Reader;
use Magento\Framework\Config\CacheInterface;
use Magento\Framework\Config\ScopeInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    /**
     * @var Config
     */
    protected $_config;

    /**
     * @var Reader|MockObject
     */
    protected $_readerMock;

    /**
     * @var MockObject
     */
    protected $_cacheMock;

    /**
     * @var MockObject
     */
    protected $_configScopeMock;

    /**
     * @var MockObject
     */
    protected $_areaList;

    /**
     * @var SerializerInterface|MockObject
     */
    private $serializerMock;

    protected function setUp(): void
    {
        $this->_readerMock = $this->createMock(Reader::class);
        $this->_cacheMock = $this->getMockForAbstractClass(CacheInterface::class);
        $this->_configScopeMock = $this->getMockForAbstractClass(ScopeInterface::class);
        $this->_areaList = $this->createMock(AreaList::class);
        $this->_configScopeMock->expects($this->any())
            ->method('getCurrentScope')
            ->willReturn('areaCode');
        $objectManager = new ObjectManager($this);
        $this->_config = $objectManager->getObject(
            Config::class,
            [
                'reader' => $this->_readerMock,
                'cache' => $this->_cacheMock,
                'configScope' => $this->_configScopeMock,
                'areaList' => $this->_areaList
            ]
        );
        $this->serializerMock = $this->getMockForAbstractClass(SerializerInterface::class);
        $objectManager->setBackwardCompatibleProperty($this->_config, 'serializer', $this->serializerMock);
    }

    public function testGetRouteFrontNameIfCacheIfRouterIdNotExist()
    {
        $this->_cacheMock->expects($this->once())
            ->method('load')
            ->with('areaCode::RoutesConfig')
            ->willReturn('["expected"]');
        $this->assertEquals('routerCode', $this->_config->getRouteFrontName('routerCode'));
    }

    public function testGetRouteByFrontName()
    {
        $data = ['routerCode' => ['frontName' => 'routerName']];
        $this->_cacheMock->expects($this->once())
            ->method('load')
            ->with('areaCode::RoutesConfig')
            ->willReturn('serializedData');
        $this->serializerMock->expects($this->once())
            ->method('unserialize')
            ->with('serializedData')
            ->willReturn($data);
        $this->assertEquals('routerCode', $this->_config->getRouteByFrontName('routerName'));
    }

    public function testGetRouteByFrontNameNoRoutes()
    {
        $this->_cacheMock->expects($this->once())
            ->method('load')
            ->with('areaCode::RoutesConfig')
            ->willReturn('serializedData');
        $this->serializerMock->expects($this->once())
            ->method('unserialize')
            ->with('serializedData')
            ->willReturn([]);
        $this->assertFalse($this->_config->getRouteByFrontName('routerName'));
    }

    public function testGetRouteByFrontNameNoCache()
    {
        $this->_cacheMock->expects($this->once())
            ->method('load')
            ->with('scope::RoutesConfig')
            ->willReturn('false');

        $routes = [
            'routerCode' => [
                'frontName' => 'routerName',
            ],
        ];

        $routers = [
            'default_router' => [
                'routes' => $routes,
            ],
        ];

        $serializedData = json_encode($routes);

        $this->_readerMock->expects(
            $this->once()
        )->method(
            'read'
        )->with(
            'scope'
        )->willReturn(
            $routers
        );

        $this->_areaList->expects(
            $this->once()
        )->method(
            'getDefaultRouter'
        )->with(
            'scope'
        )->willReturn(
            'default_router'
        );

        $this->serializerMock->expects($this->once())
            ->method('serialize')
            ->willReturn($serializedData);

        $this->_cacheMock->expects($this->once())
            ->method('save')
            ->with($serializedData, 'scope::RoutesConfig');

        $this->assertEquals('routerCode', $this->_config->getRouteByFrontName('routerName', 'scope'));
    }

    public function testGetModulesByFrontName()
    {
        $data = ['routerCode' => ['frontName' => 'routerName', 'modules' => ['Module1']]];

        $this->_cacheMock->expects($this->once())
            ->method('load')
            ->with('areaCode::RoutesConfig')
            ->willReturn('serializedData');
        $this->serializerMock->expects($this->once())
            ->method('unserialize')
            ->with('serializedData')
            ->willReturn($data);
        $this->assertEquals(['Module1'], $this->_config->getModulesByFrontName('routerName'));
    }
}

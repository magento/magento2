<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Webapi\Test\Unit\Controller\Rest;

use Magento\Framework\App\AreaList;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Webapi\Controller\Rest\Router;
use Magento\Webapi\Controller\Rest\Router\Route;
use Magento\Webapi\Model\Rest\Config;
use PHPUnit\Framework\TestCase;

class RouterTest extends TestCase
{
    /** @var Route */
    protected $_routeMock;

    /** @var Request */
    protected $_request;

    /** @var Config */
    protected $_apiConfigMock;

    /** @var Router */
    protected $_router;

    protected function setUp(): void
    {
        /** Prepare mocks for SUT constructor. */
        $this->_apiConfigMock = $this->getMockBuilder(
            Config::class
        )->disableOriginalConstructor()
            ->getMock();

        $this->_routeMock = $this->getMockBuilder(
            Route::class
        )->disableOriginalConstructor()
            ->onlyMethods(
                ['match']
            )->getMock();

        $areaListMock = $this->createMock(AreaList::class);

        $areaListMock->expects($this->once())
            ->method('getFrontName')
            ->willReturn('rest');

        $objectManager = new ObjectManager($this);
        $this->_request = $objectManager->getObject(
            Request::class,
            [
                'areaList' => $areaListMock,
            ]
        );

        /** Initialize SUT. */
        $this->_router = $objectManager->getObject(
            Router::class,
            [
                'apiConfig' => $this->_apiConfigMock
            ]
        );
    }

    protected function tearDown(): void
    {
        unset($this->_routeMock);
        unset($this->_request);
        unset($this->_apiConfigMock);
        unset($this->_router);
        parent::tearDown();
    }

    public function testMatch()
    {
        $this->_apiConfigMock->expects(
            $this->once()
        )->method(
            'getRestRoutes'
        )->willReturn(
            [$this->_routeMock]
        );
        $this->_routeMock->expects(
            $this->once()
        )->method(
            'match'
        )->with(
            $this->_request
        )->willReturn(
            []
        );

        $matchedRoute = $this->_router->match($this->_request);
        $this->assertEquals($this->_routeMock, $matchedRoute);
    }

    public function testNotMatch()
    {
        $this->expectException('Magento\Framework\Webapi\Exception');
        $this->_apiConfigMock->expects(
            $this->once()
        )->method(
            'getRestRoutes'
        )->willReturn(
            [$this->_routeMock]
        );
        $this->_routeMock->expects(
            $this->once()
        )->method(
            'match'
        )->with(
            $this->_request
        )->willReturn(
            false
        );

        $this->_router->match($this->_request);
    }
}

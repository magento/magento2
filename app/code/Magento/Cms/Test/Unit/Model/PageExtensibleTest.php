<?php

declare(strict_types=1);

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Cms\Test\Unit\Model;

use Magento\Cms\Helper\Page;
use Magento\Cms\Model\PageExtensible;
use Magento\Cms\Model\ResourceModel\Page as PageResource;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\Context;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Class PageExtensibleTest
 * @package Magento\Cms\Test\Unit\Model
 */
class PageExtensibleTest extends TestCase
{
    /**
     * @var PageExtensible
     */
    protected $pageExtensible;

    /**
     * @var Context|MockObject
     */
    protected $contextMock;

    /**
     * @var ManagerInterface|MockObject
     */
    protected $eventManagerMock;

    /**
     * @var PageResource|MockObject
     */
    protected $resourcePageMock;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    protected $scopeConfigMock;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->eventManagerMock = $this->getMockBuilder(ManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->eventManagerMock = $this->getMockBuilder(ManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resourcePageMock = $this->getMockBuilder(PageResource::class)
            ->disableOriginalConstructor()
            ->setMethods(['getIdFieldName', 'load', 'checkIdentifier'])
            ->getMock();
        $this->scopeConfigMock = $this->getMockBuilder(ScopeConfigInterface::class)
            ->getMock();

        $this->contextMock->expects($this->any())
            ->method('getEventDispatcher')
            ->willReturn($this->eventManagerMock);
        /*$this->resourcePageMock->expects($this->any())
            ->method('getResources')
            ->willReturn($this->resourceMock);*/

        $objectManager = new ObjectManager($this);

        $this->pageExtensible = $objectManager->getObject(
            PageExtensible::class,
            [
                'context' => $this->contextMock,
                'resource' => $this->resourcePageMock,
                'scopeConfig' => $this->scopeConfigMock
            ]
        );
    }

    /**
     * Test getStores method
     *
     * @test
     *
     * @return void
     */
    public function testGetStoresWhenDataIsStoredUnderStoresKey()
    {
        $stores = [1, 4, 9];
        $this->pageExtensible->setData('stores', $stores);
        $expected = $stores;
        $actual = $this->pageExtensible->getStores();
        $this->assertSame($expected, $actual);
    }

    /**
     * Test getStores method
     *
     * @test
     *
     * @return void
     */
    public function testGetStoresWhenDataIsStoreIdKey()
    {
        $stores = [1, 4, 9];
        $this->pageExtensible->setData('store_id', $stores);
        $expected = $stores;
        $actual = $this->pageExtensible->getStores();
        $this->assertSame($expected, $actual);
    }

    /**
     * Test getStores method
     *
     * @test
     *
     * @return void
     */
    public function testGetStoresWhenThereIsNoStoreData()
    {
        $actual = $this->pageExtensible->getStores();
        $this->assertSame([], $actual);
    }

    /**
     * @test
     *
     * @return void
     */
    public function testNoRoutePage(): void
    {
        $this->assertEquals($this->pageExtensible, $this->pageExtensible->noRoutePage());
    }

    /**
     * @test
     *
     * @return void
     * @throws LocalizedException
     */
    public function testCheckIdentifier(): void
    {
        $identifier = '1';
        $storeId = 2;
        $fetchOneResult = 'some result';

        $this->resourcePageMock->expects($this->atLeastOnce())
            ->method('checkIdentifier')
            ->with($identifier, $storeId)
            ->willReturn($fetchOneResult);

        $this->assertInternalType('int', $this->pageExtensible->checkIdentifier($identifier, $storeId));
    }

    /**
     * @test
     * @expectedExceptionMessage This identifier is reserved for "CMS No Route Page" in configuration.
     *
     * @return void
     * @throws LocalizedException
     */
    public function testBeforeSave404Identifier(): void
    {
        $this->pageExtensible->setId(1);
        $this->pageExtensible->setOrigData('identifier', 'no-route');
        $this->pageExtensible->setIdentifier('no-route2');

        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->willReturnMap(
                [
                    [
                        Page::XML_PATH_NO_ROUTE_PAGE,
                        ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                        null,
                        'no-route'
                    ]
                ]
            );

        $this->expectException(LocalizedException::class);
        $this->pageExtensible->beforeSave();
    }

    /**
     * @test
     * @expectedExceptionMessage This identifier is reserved for "CMS Home Page" in configuration.
     *
     * @return void
     * @throws LocalizedException
     */
    public function testBeforeSaveHomeIdentifier(): void
    {
        $this->pageExtensible->setId(1);
        $this->pageExtensible->setOrigData('identifier', 'home');
        $this->pageExtensible->setIdentifier('home2');

        $this->scopeConfigMock->expects($this->atLeastOnce())
            ->method('getValue')
            ->willReturnMap(
                [
                    [
                        Page::XML_PATH_HOME_PAGE,
                        ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                        null,
                        'home'
                    ]
                ]
            );

        $this->expectException(LocalizedException::class);
        $this->pageExtensible->beforeSave();
    }

    /**
     * @test
     * @expectedExceptionMessage This identifier is reserved for "CMS No Cookies Page" in configuration.
     *
     * @return void
     */
    public function testBeforeSaveNoCookiesIdentifier(): void
    {
        $this->pageExtensible->setId(1);
        $this->pageExtensible->setOrigData('identifier', 'no-cookies');
        $this->pageExtensible->setIdentifier('no-cookies2');

        $this->scopeConfigMock->expects($this->atLeastOnce())
            ->method('getValue')
            ->willReturnMap(
                [
                    [
                        Page::XML_PATH_NO_COOKIES_PAGE,
                        ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                        null,
                        'no-cookies'
                    ]
                ]
            );

        $this->expectException(LocalizedException::class);
        $this->pageExtensible->beforeSave();
    }
}

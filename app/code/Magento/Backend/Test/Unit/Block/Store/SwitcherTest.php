<?php
/**
 * Copyright 2024 Adobe
 * All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Backend\Test\Unit\Block\Store;

use Magento\Backend\Block\Store\Switcher;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\StoreFactory;
use Magento\Store\Model\Store;
use Magento\Store\Model\WebsiteFactory;
use Magento\Store\Model\Website;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SwitcherTest extends TestCase
{
    /**
     * @var Switcher
     */
    private $switcherBlock;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManagerMock;

    /**
     * @var RequestInterface|MockObject
     */
    private $requestMock;

    /**
     * @var WebsiteFactory|MockObject
     */
    private $websiteFactoryMock;

    /**
     * @var StoreFactory|MockObject
     */
    private $storeFactoryMock;

    /**
     * @var Website|MockObject
     */
    private $websiteMock;

    /**
     * @var Store|MockObject
     */
    private $storeMock;

    protected function setUp(): void
    {
        $this->storeManagerMock = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $objectHelper = new ObjectManager($this);
        $this->requestMock = $this->getMockBuilder(RequestInterface::class)
            ->getMockForAbstractClass();
        $this->websiteFactoryMock = $this->getMockBuilder(WebsiteFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $this->storeFactoryMock = $this->getMockBuilder(StoreFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $this->websiteMock = $this->getMockBuilder(Website::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['load', 'getId', 'getName'])
            ->getMock();
        $this->storeMock = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['load', 'getId', 'getName'])
            ->getMock();
        $this->websiteFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->websiteMock);
        $this->storeFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->storeMock);
        $this->websiteMock->expects($this->any())
            ->method('load')
            ->willReturnSelf();
        $this->storeMock->expects($this->any())
            ->method('load')
            ->willReturnSelf();
        $context = $objectHelper->getObject(
            Context::class,
            [
                'storeManager' => $this->storeManagerMock,
                'request' => $this->requestMock
            ]
        );

        $this->switcherBlock = $objectHelper->getObject(
            Switcher::class,
            [
                'context' => $context,
                'data' => ['get_data_from_request' => 1],
                'websiteFactory' => $this->websiteFactoryMock,
                'storeFactory' => $this->storeFactoryMock
            ]
        );
    }

    public function testGetWebsites()
    {
        $websiteMock =  $this->createMock(Website::class);
        $websites = [0 => $websiteMock, 1 => $websiteMock];
        $this->storeManagerMock->expects($this->once())->method('getWebsites')->willReturn($websites);
        $this->assertEquals($websites, $this->switcherBlock->getWebsites());
    }

    public function testGetWebsitesIfSetWebsiteIds()
    {
        $websiteMock =  $this->createMock(Website::class);
        $websites = [0 => $websiteMock, 1 => $websiteMock];
        $this->storeManagerMock->expects($this->once())->method('getWebsites')->willReturn($websites);

        $this->switcherBlock->setWebsiteIds([1]);
        $expected = [1 => $websiteMock];
        $this->assertEquals($expected, $this->switcherBlock->getWebsites());
    }

    /**
     * Test case for after current store name plugin
     *
     * @param array $requestedStore
     * @param string $expectedResult
     * @return void
     * @dataProvider getStoreNameDataProvider
     * @throws LocalizedException
     */
    public function testAfterGetCurrentStoreName(array $requestedStore, string $expectedResult): void
    {
        $this->requestMock->expects($this->any())
            ->method('getParams')
            ->willReturn($requestedStore);
        $this->storeMock->expects($this->any())
            ->method('getId')
            ->willReturn($requestedStore);
        $this->storeMock->expects($this->any())
            ->method('getName')
            ->willReturn($expectedResult);
        $this->assertSame($expectedResult, $this->switcherBlock->getCurrentStoreName());
    }

    /**
     * Data provider for getStoreName plugin
     *
     * @return array
     */
    public static function getStoreNameDataProvider(): array
    {
        return [
            'test storeName with valid requested store' =>
                [
                    ['store' => 'test store'],
                    'base store'
                ],
            'test storeName with invalid requested store' =>
                [
                    ['store' => 'test store'],
                    'test store'
                ]
        ];
    }

    /**
     * Test case for get current website name
     *
     * @param array $requestedWebsite
     * @param string $expectedResult
     * @return void
     * @dataProvider getWebsiteNameDataProvider
     */
    public function testGetCurrentWebsiteName(array $requestedWebsite, string $expectedResult): void
    {
        $this->requestMock->expects($this->any())
            ->method('getParams')
            ->willReturn($requestedWebsite);
        $this->websiteMock->expects($this->any())
            ->method('getId')
            ->willReturn($requestedWebsite);
        $this->websiteMock->expects($this->any())
            ->method('getName')
            ->willReturn($expectedResult);
        $this->assertSame($expectedResult, $this->switcherBlock->getCurrentWebsiteName());
    }

    /**
     * Data provider for getWebsiteName plugin
     *
     * @return array
     */
    public static function getWebsiteNameDataProvider(): array
    {
        return [
            'test websiteName with valid requested website' =>
                [
                    ['website' => 'test website'],
                    'base website'
                ],
            'test websiteName with invalid requested website' =>
                [
                    ['website' => 'test website'],
                    'test website'
                ]
        ];
    }
}

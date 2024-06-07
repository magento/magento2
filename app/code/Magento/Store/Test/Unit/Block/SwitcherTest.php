<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Store\Test\Unit\Block;

use Magento\Directory\Helper\Data;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Helper\PostHelper;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Block\Switcher;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Website;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SwitcherTest extends TestCase
{
    /**
     * @var Switcher
     */
    private $switcher;

    /**
     * @var PostHelper|MockObject
     */
    private $corePostDataHelperMock;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManagerMock;

    /**
     * @var UrlInterface|MockObject
     */
    private $urlBuilderMock;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfigMock;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->storeManagerMock = $this->getMockBuilder(StoreManagerInterface::class)->getMock();
        $this->urlBuilderMock = $this->createMock(UrlInterface::class);
        $this->scopeConfigMock = $this->createMock(ScopeConfigInterface::class);
        $contextMock = $this->createMock(Context::class);
        $contextMock->method('getStoreManager')->willReturn($this->storeManagerMock);
        $contextMock->method('getUrlBuilder')->willReturn($this->urlBuilderMock);
        $contextMock->method('getScopeConfig')->willReturn($this->scopeConfigMock);
        $this->corePostDataHelperMock = $this->createMock(PostHelper::class);
        $this->switcher = (new ObjectManager($this))->getObject(
            Switcher::class,
            [
                'context' => $contextMock,
                'postDataHelper' => $this->corePostDataHelperMock,
            ]
        );
    }

    public function testGetStoresSortOrder()
    {
        $groupId = 1;
        $storesSortOrder = [
            1 => 2,
            2 => 4,
            3 => 1,
            4 => 3
        ];

        $currentStoreMock = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->getMock();
        $currentStoreMock->method('getGroupId')->willReturn($groupId);
        $currentStoreMock->method('isUseStoreInUrl')->willReturn(false);
        $this->storeManagerMock->method('getStore')
            ->willReturn($currentStoreMock);

        $currentWebsiteMock = $this->getMockBuilder(Website::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManagerMock->method('getWebsite')
            ->willReturn($currentWebsiteMock);

        $stores = [];
        foreach ($storesSortOrder as $storeId => $sortOrder) {
            $storeMock = $this->getMockBuilder(Store::class)
                ->disableOriginalConstructor()
                ->addMethods(['getSortOrder'])
                ->onlyMethods(['getId', 'getGroupId', 'isActive', 'getUrl'])
                ->getMock();
            $storeMock->method('getId')->willReturn($storeId);
            $storeMock->method('getGroupId')->willReturn($groupId);
            $storeMock->method('getSortOrder')->willReturn($sortOrder);
            $storeMock->method('isActive')->willReturn(true);
            $storeMock->method('getUrl')->willReturn('https://example.org');
            $stores[] = $storeMock;
        }

        $scopeConfigMap = array_map(static function ($item) {
            return [
                Data::XML_PATH_DEFAULT_LOCALE,
                ScopeInterface::SCOPE_STORE,
                $item,
                'en_US'
            ];
        }, $stores);
        $this->scopeConfigMock->method('getValue')
            ->willReturnMap($scopeConfigMap);

        $currentWebsiteMock->method('getStores')
            ->willReturn($stores);

        $this->assertEquals([3, 1, 4, 2], array_keys($this->switcher->getStores()));
    }

    /**
     * @return void
     */
    public function testGetTargetStorePostData()
    {
        $storeMock = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->getMock();
        $oldStoreMock = $this->getMockBuilder(StoreInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $storeMock->method('getCode')
            ->willReturn('new-store');
        $storeSwitchUrl = 'http://domain.com/stores/store/redirect';
        $storeMock->expects($this->atLeastOnce())
            ->method('getCurrentUrl')
            ->with(false)
            ->willReturn($storeSwitchUrl);
        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->willReturn($oldStoreMock);
        $oldStoreMock->expects($this->once())
            ->method('getCode')
            ->willReturn('old-store');
        $this->urlBuilderMock->expects($this->once())
            ->method('getUrl')
            ->willReturn($storeSwitchUrl);
        $this->corePostDataHelperMock->method('getPostData')
            ->with($storeSwitchUrl, ['___store' => 'new-store', 'uenc' => null, '___from_store' => 'old-store']);

        $this->switcher->getTargetStorePostData($storeMock);
    }

    /**
     * @dataProvider isStoreInUrlDataProvider
     * @param bool $isUseStoreInUrl
     */
    public function testIsStoreInUrl($isUseStoreInUrl)
    {
        $storeMock = $this->createMock(Store::class);

        $storeMock->expects($this->once())->method('isUseStoreInUrl')->willReturn($isUseStoreInUrl);

        $this->storeManagerMock->method('getStore')->willReturn($storeMock);
        $this->assertEquals($this->switcher->isStoreInUrl(), $isUseStoreInUrl);
        // check value is cached
        $this->assertEquals($this->switcher->isStoreInUrl(), $isUseStoreInUrl);
    }

    /**
     * @see self::testIsStoreInUrlDataProvider()
     * @return array
     */
    public static function isStoreInUrlDataProvider(): array
    {
        return [[true], [false]];
    }
}

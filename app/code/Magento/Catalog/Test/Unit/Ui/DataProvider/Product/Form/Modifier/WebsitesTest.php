<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\Websites;
use Magento\Store\Api\GroupRepositoryInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\Store\Model\Group;
use Magento\Store\Model\ResourceModel\Group\Collection;
use Magento\Store\Model\Store as StoreView;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Website;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class WebsitesTest extends AbstractModifierTest
{
    public const PRODUCT_ID = 1;
    public const WEBSITE_ID = 1;
    public const GROUP_ID = 1;
    public const STORE_VIEW_NAME = 'StoreView';
    public const STORE_VIEW_ID = 1;
    public const SECOND_WEBSITE_ID = 2;

    /**
     * @var WebsiteRepositoryInterface|MockObject
     */
    protected $websiteRepositoryMock;

    /**
     * @var GroupRepositoryInterface|MockObject
     */
    protected $groupRepositoryMock;

    /**
     * @var StoreRepositoryInterface|MockObject
     */
    protected $storeRepositoryMock;

    /**
     * @var StoreManagerInterface|MockObject
     */
    protected $storeManagerMock;

    /**
     * @var Website|MockObject
     */
    protected $websiteMock;

    /**
     * @var Website|MockObject
     */
    protected $secondWebsiteMock;

    /**
     * @var array
     */
    protected $assignedWebsites;

    /**
     * @var Group|MockObject
     */
    protected $groupMock;

    /**
     * @var StoreView|MockObject
     */
    protected $storeViewMock;

    /**
     * @var array
     */
    private $websitesList;

    /**
     * @var int
     */
    private $productId;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->assignedWebsites = [self::SECOND_WEBSITE_ID];
        $this->productId = self::PRODUCT_ID;
        $this->websiteMock = $this->getMockBuilder(Website::class)
            ->setMethods(['getId', 'getName'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->secondWebsiteMock = $this->getMockBuilder(Website::class)
            ->setMethods(['getId', 'getName'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->websitesList = [$this->websiteMock, $this->secondWebsiteMock];
        $this->websiteRepositoryMock = $this->getMockBuilder(WebsiteRepositoryInterface::class)
            ->setMethods(['getList'])
            ->getMockForAbstractClass();
        $this->websiteRepositoryMock->expects($this->any())
            ->method('getDefault')
            ->willReturn($this->websiteMock);
        $this->groupRepositoryMock = $this->getMockBuilder(GroupRepositoryInterface::class)
            ->setMethods(['getList'])
            ->getMockForAbstractClass();
        $this->storeRepositoryMock = $this->getMockBuilder(StoreRepositoryInterface::class)
            ->setMethods(['getList'])
            ->getMockForAbstractClass();
        $this->storeManagerMock = $this->getMockBuilder(StoreManagerInterface::class)
            ->setMethods(['isSingleStoreMode', 'getWebsites'])
            ->getMockForAbstractClass();
        $this->storeManagerMock->expects($this->any())
            ->method('isSingleStoreMode')
            ->willReturn(false);
        $this->groupMock = $this->getMockBuilder(Collection::class)
            ->setMethods(['getId', 'getName', 'getWebsiteId'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->groupMock->expects($this->any())
            ->method('getWebsiteId')
            ->willReturn(self::WEBSITE_ID);
        $this->groupMock->expects($this->any())
            ->method('getId')
            ->willReturn(self::GROUP_ID);
        $this->groupRepositoryMock->expects($this->any())
            ->method('getList')
            ->willReturn([$this->groupMock]);
        $this->storeViewMock = $this->getMockBuilder(\Magento\Store\Model\Store::class)
            ->setMethods(['getName', 'getId', 'getStoreGroupId'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeViewMock->expects($this->any())
            ->method('getName')
            ->willReturn(self::STORE_VIEW_NAME);
        $this->storeViewMock->expects($this->any())
            ->method('getStoreGroupId')
            ->willReturn(self::GROUP_ID);
        $this->storeViewMock->expects($this->any())
            ->method('getId')
            ->willReturn(self::STORE_VIEW_ID);
        $this->storeRepositoryMock->expects($this->any())
            ->method('getList')
            ->willReturn([$this->storeViewMock]);
        $this->secondWebsiteMock->expects($this->any())
            ->method('getId')
            ->willReturn($this->assignedWebsites[0]);
        $this->websiteMock->expects($this->any())
            ->method('getId')
            ->willReturn(self::WEBSITE_ID);
    }

    /**
     * @return Websites
     */
    protected function createModel()
    {
        return $this->objectManager->getObject(
            Websites::class,
            [
                'locator' => $this->locatorMock,
                'storeManager' => $this->storeManagerMock,
                'websiteRepository' => $this->websiteRepositoryMock,
                'groupRepository' => $this->groupRepositoryMock,
                'storeRepository' => $this->storeRepositoryMock,
            ]
        );
    }

    /**
     * Initialize return values
     * @return void
     */
    private function init()
    {
        $this->productMock->expects($this->any())
            ->method('getId')
            ->willReturn($this->productId);
        $this->locatorMock->expects($this->any())
            ->method('getWebsiteIds')
            ->willReturn($this->assignedWebsites);
        $this->storeManagerMock->method('getWebsites')
            ->willReturn($this->websitesList);
    }

    /**
     * @return void
     */
    public function testModifyMeta()
    {
        $this->init();
        $meta = $this->getModel()->modifyMeta([]);

        $this->assertArrayHasKey('websites', $meta);
        $this->assertArrayHasKey(self::SECOND_WEBSITE_ID, $meta['websites']['children']);
        $this->assertArrayHasKey(self::WEBSITE_ID, $meta['websites']['children']);
        $this->assertArrayHasKey('copy_to_stores.' . self::WEBSITE_ID, $meta['websites']['children']);
        $this->assertEquals(
            $meta['websites']['children'][self::SECOND_WEBSITE_ID]['arguments']['data']['config']['value'],
            (string) self::SECOND_WEBSITE_ID
        );
        $this->assertEquals(
            $meta['websites']['children'][self::WEBSITE_ID]['arguments']['data']['config']['value'],
            '0'
        );
    }

    /**
     * @return void
     */
    public function testModifyData()
    {
        $expectedData = [
            self::PRODUCT_ID => [
                'product' => [
                    'copy_to_stores' => [
                        self::WEBSITE_ID => [
                            [
                                'storeView' => self::STORE_VIEW_NAME,
                                'copy_from' => 0,
                                'copy_to' => self::STORE_VIEW_ID,
                            ]
                        ]
                    ]
                ]
            ],
        ];
        $this->init();

        $this->assertEquals(
            $expectedData,
            $this->getModel()->modifyData([])
        );
    }

    public function testModifyDataNoWebsitesExistingProduct()
    {
        $this->assignedWebsites = [];
        $this->websitesList = [$this->websiteMock];
        $this->init();

        $meta = $this->getModel()->modifyMeta([]);

        $this->assertArrayHasKey(self::WEBSITE_ID, $meta['websites']['children']);
        $this->assertArrayHasKey('copy_to_stores.' . self::WEBSITE_ID, $meta['websites']['children']);
        $this->assertEquals(
            '0',
            $meta['websites']['children'][self::WEBSITE_ID]['arguments']['data']['config']['value']
        );
    }

    public function testModifyDataNoWebsitesNewProduct()
    {
        $this->assignedWebsites = [];
        $this->websitesList = [$this->websiteMock];
        $this->productId = false;
        $this->init();
        $this->productMock->expects($this->any())
            ->method('getId')
            ->willReturn(false);

        $meta = $this->getModel()->modifyMeta([]);

        $this->assertArrayHasKey(self::WEBSITE_ID, $meta['websites']['children']);
        $this->assertEquals(
            '1',
            $meta['websites']['children'][self::WEBSITE_ID]['arguments']['data']['config']['value']
        );
    }
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\Websites;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\Store\Api\GroupRepositoryInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\Website;
use Magento\Store\Model\Store as StoreView;
use Magento\Store\Model\Group;

/**
 * Class WebsitesTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class WebsitesTest extends AbstractModifierTest
{
    const PRODUCT_ID = 1;
    const WEBSITE_ID = 1;
    const GROUP_ID = 1;
    const STORE_VIEW_NAME = 'StoreView';
    const STORE_VIEW_ID = 1;
    const SECOND_WEBSITE_ID = 2;

    /**
     * @var WebsiteRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $websiteRepositoryMock;

    /**
     * @var GroupRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $groupRepositoryMock;

    /**
     * @var StoreRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeRepositoryMock;

    /**
     * @var StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManagerMock;

    /**
     * @var Website|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $websiteMock;

    /**
     * @var Website|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $secondWebsiteMock;

    /**
     * @var array
     */
    protected $assignedWebsites;

    /**
     * @var Group|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $groupMock;

    /**
     * @var StoreView|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeViewMock;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();
        $this->productMock->expects($this->any())
            ->method('getId')
            ->willReturn(self::PRODUCT_ID);
        $this->assignedWebsites = [self::SECOND_WEBSITE_ID];
        $this->websiteMock = $this->getMockBuilder(\Magento\Store\Model\Website::class)
            ->setMethods(['getId', 'getName'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->secondWebsiteMock = $this->getMockBuilder(\Magento\Store\Model\Website::class)
            ->setMethods(['getId', 'getName'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->websiteRepositoryMock = $this->getMockBuilder(\Magento\Store\Api\WebsiteRepositoryInterface::class)
            ->setMethods(['getList'])
            ->getMockForAbstractClass();
        $this->websiteRepositoryMock->expects($this->any())
            ->method('getDefault')
            ->willReturn($this->websiteMock);
        $this->groupRepositoryMock = $this->getMockBuilder(\Magento\Store\Api\GroupRepositoryInterface::class)
            ->setMethods(['getList'])
            ->getMockForAbstractClass();
        $this->storeRepositoryMock = $this->getMockBuilder(\Magento\Store\Api\StoreRepositoryInterface::class)
            ->setMethods(['getList'])
            ->getMockForAbstractClass();
        $this->productMock = $this->getMockBuilder(\Magento\Catalog\Api\Data\ProductInterface::class)
            ->setMethods(['getId'])
            ->getMockForAbstractClass();
        $this->locatorMock->expects($this->any())
            ->method('getWebsiteIds')
            ->willReturn($this->assignedWebsites);
        $this->storeManagerMock = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->setMethods(['isSingleStoreMode', 'getWesites'])
            ->getMockForAbstractClass();
        $this->storeManagerMock->method('getWebsites')
            ->willReturn([$this->websiteMock, $this->secondWebsiteMock]);
        $this->storeManagerMock->expects($this->any())
            ->method('isSingleStoreMode')
            ->willReturn(false);
        $this->groupMock = $this->getMockBuilder(\Magento\Store\Model\ResourceModel\Group\Collection::class)
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
     * @return \Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\Websites
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
     * @return void
     */
    public function testModifyMeta()
    {
        $meta = $this->getModel()->modifyMeta([]);
        $this->assertTrue(isset($meta['websites']));
        $this->assertTrue(isset($meta['websites']['children'][self::SECOND_WEBSITE_ID]));
        $this->assertTrue(isset($meta['websites']['children'][self::WEBSITE_ID]));
        $this->assertTrue(isset($meta['websites']['children']['copy_to_stores.' . self::WEBSITE_ID]));
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

        $this->assertEquals(
            $expectedData,
            $this->getModel()->modifyData([])
        );
    }
}

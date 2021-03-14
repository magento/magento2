<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Model\Plugin;

use Magento\Catalog\Model\Indexer\Product\Price\Processor;
use Magento\Customer\Api\Data\GroupExtensionInterface;
use Magento\Customer\Api\Data\GroupInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Customer\Api\GroupExcludedWebsiteRepositoryInterface;
use Magento\Customer\Model\Data\GroupExcludedWebsite;
use Magento\Customer\Model\Data\GroupExcludedWebsiteFactory;
use Magento\Customer\Model\Plugin\SaveCustomerGroupExcludedWebsite;
use Magento\Customer\Model\ResourceModel\GroupExcludedWebsite as GroupExcludedWebsiteResourceModel;
use Magento\Framework\Indexer\IndexerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\System\Store;
use Magento\Store\Model\Website;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SaveCustomerGroupExcludedWebsiteTest extends TestCase
{
    /**
     * @var GroupInterface|MockObject
     */
    private $groupInterface;

    /**
     * @var GroupExtensionInterface|MockObject
     */
    private $groupExtensionInterface;

    /**
     * @var GroupRepositoryInterface|MockObject
     */
    private $groupRepositoryInterface;

    /**
     * @var GroupExcludedWebsiteFactory|MockObject
     */
    private $groupExcludedWebsiteFactory;

    /**
     * @var GroupExcludedWebsite|MockObject
     */
    private $groupExcludedWebsite;

    /**
     * @var GroupExcludedWebsiteRepositoryInterface|MockObject
     */
    private $groupExcludedWebsiteRepository;

    /**
     * @var GroupExcludedWebsiteResourceModel|MockObject
     */
    private $groupExcludedWebsiteResourceModel;

    /**
     * @var Store|MockObject
     */
    private $store;

    /**
     * @var Processor|MockObject
     */
    private $priceIndexProcessor;

    /**
     * @var IndexerInterface
     */
    private $priceIndexer;

    /**
     * @var  SaveCustomerGroupExcludedWebsite
     */
    private $plugin;

    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManager($this);

        $this->groupExcludedWebsiteFactory = $this->getMockBuilder(GroupExcludedWebsiteFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->groupExcludedWebsiteRepository = $this->getMockForAbstractClass(
            GroupExcludedWebsiteRepositoryInterface::class
        );
        $this->groupExcludedWebsiteResourceModel = $this->createMock(GroupExcludedWebsiteResourceModel::class);
        $this->groupExcludedWebsite = $this->getMockBuilder(GroupExcludedWebsite::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->groupRepositoryInterface = $this->getMockBuilder(GroupRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->groupInterface = $this->getMockBuilder(GroupInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->groupExtensionInterface = $this->getMockBuilder(GroupExtensionInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->groupInterface->method('getExtensionAttributes')
            ->willReturn($this->groupExtensionInterface);
        $this->groupInterface->method('getId')->willReturn(1);

        $this->store = $this->createPartialMock(
            Store::class,
            ['getWebsiteCollection', 'getGroupCollection', 'getStoreCollection']
        );
        $this->priceIndexProcessor = $this->getMockBuilder(Processor::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->priceIndexer = $this->getMockBuilder(IndexerInterface::class)
            ->getMockForAbstractClass();

        $this->plugin = $objectManagerHelper->getObject(
            SaveCustomerGroupExcludedWebsite::class,
            [
                'groupExcludedWebsiteFactory' => $this->groupExcludedWebsiteFactory,
                'groupExcludedWebsiteRepository' => $this->groupExcludedWebsiteRepository,
                'systemStore' => $this->store,
                'priceIndexProcessor' => $this->priceIndexProcessor
            ]
        );
    }

    public function testAfterSaveWithoutExtensionAttributes(): void
    {
        $this->groupExtensionInterface->method('getExcludeWebsiteIds')->willReturn(null);
        $this->groupInterface->expects(self::never())->method('getId');

        $this->plugin->afterSave($this->groupRepositoryInterface, $this->groupInterface, $this->groupInterface);
    }

    /**
     * @dataProvider dataProviderNoExcludedWebsitesChanged
     * @param array $excludedWebsites
     * @param array $websitesToExclude
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function testAfterSaveWithNoExcludedWebsitesChanged(array $excludedWebsites, array $websitesToExclude): void
    {
        $this->getAllWebsites();

        $this->groupExtensionInterface->method('getExcludeWebsiteIds')->willReturn($websitesToExclude);
        $this->groupExcludedWebsiteRepository->method('getCustomerGroupExcludedWebsites')
            ->with(1)->willReturn($excludedWebsites);
        $this->groupExcludedWebsiteRepository->expects(self::never())->method('delete');
        $this->groupExcludedWebsiteFactory->expects(self::never())->method('create');

        $this->plugin->afterSave($this->groupRepositoryInterface, $this->groupInterface, $this->groupInterface);
    }

    /**
     * @dataProvider dataProviderExcludedWebsitesChanged
     * @param array $excludedWebsites
     * @param array $websitesToExclude
     * @param int $times
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function testAfterSaveWithExcludedWebsitesChanged(
        array $excludedWebsites,
        array $websitesToExclude,
        int $times
    ): void {
        $this->getAllWebsites();

        $this->groupExtensionInterface->method('getExcludeWebsiteIds')->willReturn($websitesToExclude);
        $this->groupExcludedWebsiteRepository->method('getCustomerGroupExcludedWebsites')
            ->with(1)->willReturn($excludedWebsites);
        $this->groupExcludedWebsiteRepository->expects(self::once())->method('delete');
        $this->groupExcludedWebsiteFactory->expects(self::exactly($times))
            ->method('create')->willReturn($this->groupExcludedWebsite);
        $this->groupExcludedWebsite->expects(self::exactly($times))
            ->method('setGroupId')
            ->with(1)
            ->willReturnSelf();
        $this->groupExcludedWebsite->expects(self::exactly($times))
            ->method('setExcludedWebsiteId')->willReturnSelf();
        $this->groupExcludedWebsiteRepository->expects(self::exactly($times))
            ->method('save')
            ->willReturn($this->groupExcludedWebsiteResourceModel);

        $this->priceIndexProcessor->expects(self::once())->method('getIndexer')
            ->willReturn($this->priceIndexer);
        $this->priceIndexer->expects(self::once())->method('invalidate')
            ->willReturnSelf();

        $this->plugin->afterSave($this->groupRepositoryInterface, $this->groupInterface, $this->groupInterface);
    }

    private function getAllWebsites(): void
    {
        $websiteMock1 = $this->getMockBuilder(Website::class)
            ->setMethods(['getWebsiteId'])
            ->disableOriginalConstructor()
            ->getMock();
        $websiteMock2 = $this->getMockBuilder(Website::class)
            ->setMethods(['getWebsiteId'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->store->expects(self::once())->method('getWebsiteCollection')
            ->willReturn([$websiteMock1, $websiteMock2]);
        $websiteMock1->method('getWebsiteId')->willReturn(1);
        $websiteMock2->method('getWebsiteId')->willReturn(2);
    }

    /**
     * Data provider for customer groups where excluded websites has not changed.
     *
     * @return array[]
     */
    public function dataProviderNoExcludedWebsitesChanged(): array
    {
        return [
            [
                [], []
            ],
            [
                ['1', '2'],
                [1, 2]
            ],
            [
                [1, 2],
                [1, 2]
            ],
            [
                [1, 2],
                ['1', '2']
            ],
            [
                ['1', 2],
                ['2', 1]
            ],
            [
                ['1', 2],
                ['2', 1, 3]
            ]
        ];
    }

    /**
     * Data provider for customer groups where excluded websites has changed.
     *
     * @return array[]
     */
    public function dataProviderExcludedWebsitesChanged(): array
    {
        return [
            [
                ['2'],
                [1, 2],
                2
            ],
            [
                [],
                [1, 2],
                2
            ],
            [
                [2],
                [1, 2],
                2
            ],
            [
                [1, 2],
                [],
                0
            ],
            [
                [1, 2],
                ['1'],
                1
            ],
            [
                ['1', 2, 3],
                ['2', 1],
                2
            ]
        ];
    }
}

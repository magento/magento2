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
use Magento\Customer\Api\GroupExcludedWebsiteRepositoryInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Customer\Model\Data\GroupExcludedWebsite;
use Magento\Customer\Model\Data\GroupExcludedWebsiteFactory;
use Magento\Customer\Model\Plugin\SaveCustomerGroupExcludedWebsite;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Indexer\IndexerInterface;
use Magento\Store\Model\System\Store;
use Magento\Store\Model\Website;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SaveCustomerGroupExcludedWebsiteTest extends TestCase
{
    /**
     * @var GroupInterface|MockObject
     */
    private $groupMock;

    /**
     * @var GroupExtensionInterface|MockObject
     */
    private $groupExtensionMock;

    /**
     * @var GroupRepositoryInterface|MockObject
     */
    private $groupRepositoryMock;

    /**
     * @var GroupExcludedWebsiteFactory|MockObject
     */
    private $groupExcludedWebsiteFactoryMock;

    /**
     * @var GroupExcludedWebsite|MockObject
     */
    private $groupExcludedWebsiteMock;

    /**
     * @var GroupExcludedWebsiteRepositoryInterface|MockObject
     */
    private $groupExcludedWebsiteRepositoryMock;

    /**
     * @var Store|MockObject
     */
    private $storeMock;

    /**
     * @var Processor|MockObject
     */
    private $priceIndexProcessorMock;

    /**
     * @var IndexerInterface
     */
    private $priceIndexerMock;

    /**
     * @var SaveCustomerGroupExcludedWebsite
     */
    private $plugin;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->groupExcludedWebsiteFactoryMock = $this->getMockBuilder(GroupExcludedWebsiteFactory::class)
            ->onlyMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->groupExcludedWebsiteRepositoryMock = $this->getMockForAbstractClass(
            GroupExcludedWebsiteRepositoryInterface::class
        );
        $this->groupExcludedWebsiteMock = $this->getMockBuilder(GroupExcludedWebsite::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->groupRepositoryMock = $this->getMockBuilder(GroupRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->groupMock = $this->getMockBuilder(GroupInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->groupExtensionMock = $this->getMockBuilder(GroupExtensionInterface::class)
            ->addMethods(['getExcludeWebsiteIds'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->groupMock->method('getExtensionAttributes')
            ->willReturn($this->groupExtensionMock);
        $this->groupMock->method('getId')->willReturn(1);

        $this->storeMock = $this->createPartialMock(
            Store::class,
            ['getWebsiteCollection', 'getGroupCollection', 'getStoreCollection']
        );
        $this->priceIndexProcessorMock = $this->getMockBuilder(Processor::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->priceIndexerMock = $this->getMockBuilder(IndexerInterface::class)
            ->getMockForAbstractClass();

        $this->plugin = new SaveCustomerGroupExcludedWebsite(
            $this->groupExcludedWebsiteFactoryMock,
            $this->groupExcludedWebsiteRepositoryMock,
            $this->storeMock,
            $this->priceIndexProcessorMock
        );
    }

    /**
     * @return void
     */
    public function testAfterSaveWithoutExtensionAttributes(): void
    {
        $this->groupExtensionMock->method('getExcludeWebsiteIds')->willReturn(null);
        $this->groupMock->expects(self::never())->method('getId');

        $this->plugin->afterSave($this->groupRepositoryMock, $this->groupMock, $this->groupMock);
    }

    /**
     * @param array $excludedWebsites
     * @param array $websitesToExclude
     *
     * @return void
     * @throws CouldNotSaveException
     * @throws LocalizedException
     * @dataProvider dataProviderNoExcludedWebsitesChanged
     */
    public function testAfterSaveWithNoExcludedWebsitesChanged(array $excludedWebsites, array $websitesToExclude): void
    {
        $this->getAllWebsites();

        $this->groupExtensionMock->method('getExcludeWebsiteIds')->willReturn($websitesToExclude);
        $this->groupExcludedWebsiteRepositoryMock->method('getCustomerGroupExcludedWebsites')
            ->with(1)->willReturn($excludedWebsites);
        $this->groupExcludedWebsiteRepositoryMock->expects(self::never())->method('delete');
        $this->groupExcludedWebsiteFactoryMock->expects(self::never())->method('create');

        $this->plugin->afterSave($this->groupRepositoryMock, $this->groupMock, $this->groupMock);
    }

    /**
     * @param array $excludedWebsites
     * @param array $websitesToExclude
     * @param int $times
     * @return void
     * @throws CouldNotSaveException
     * @throws LocalizedException
     *
     * @dataProvider dataProviderExcludedWebsitesChanged
     */
    public function testAfterSaveWithExcludedWebsitesChanged(
        array $excludedWebsites,
        array $websitesToExclude,
        int $times
    ): void {
        $this->getAllWebsites();

        $this->groupExtensionMock->method('getExcludeWebsiteIds')->willReturn($websitesToExclude);
        $this->groupExcludedWebsiteRepositoryMock->method('getCustomerGroupExcludedWebsites')
            ->with(1)->willReturn($excludedWebsites);
        $this->groupExcludedWebsiteRepositoryMock->expects(self::once())->method('delete');
        $this->groupExcludedWebsiteFactoryMock->expects(self::exactly($times))
            ->method('create')->willReturn($this->groupExcludedWebsiteMock);
        $this->groupExcludedWebsiteMock->expects(self::exactly($times))
            ->method('setGroupId')
            ->with(1)
            ->willReturnSelf();
        $this->groupExcludedWebsiteMock->expects(self::exactly($times))
            ->method('setExcludedWebsiteId')->willReturnSelf();
        $this->groupExcludedWebsiteRepositoryMock->expects(self::exactly($times))
            ->method('save')
            ->willReturn($this->groupExcludedWebsiteMock);

        $this->priceIndexProcessorMock->expects(self::once())->method('getIndexer')
            ->willReturn($this->priceIndexerMock);
        $this->priceIndexerMock->expects(self::once())->method('invalidate')
            ->willReturnSelf();

        $this->plugin->afterSave($this->groupRepositoryMock, $this->groupMock, $this->groupMock);
    }

    /**
     * @return void
     */
    private function getAllWebsites(): void
    {
        $websiteMock1 = $this->getMockBuilder(Website::class)
            ->addMethods(['getWebsiteId'])
            ->disableOriginalConstructor()
            ->getMock();
        $websiteMock2 = $this->getMockBuilder(Website::class)
            ->addMethods(['getWebsiteId'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeMock->expects(self::once())->method('getWebsiteCollection')
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
                [],
                []
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

<?php
/**
 * Copyright 2024 Adobe
 * All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdvancedSearch\Test\Unit\Model\Indexer\Fulltext\Plugin;

use Magento\AdvancedSearch\Model\Client\ClientOptionsInterface;
use Magento\AdvancedSearch\Model\Indexer\Fulltext\Plugin\CustomerGroup as CustomerGroupPlugin;
use Magento\CatalogSearch\Model\Indexer\Fulltext as FulltextIndexer;
use Magento\Customer\Model\Group as CustomerGroupModel;
use Magento\Customer\Model\ResourceModel\Group as CustomerGroupResourceModel;
use Magento\Framework\Indexer\IndexerInterface;
use Magento\Framework\Indexer\IndexerRegistry;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @covers \Magento\AdvancedSearch\Model\Indexer\Fulltext\Plugin\CustomerGroup
 */
class CustomerGroupTest extends TestCase
{
    /**
     * Testable Object
     *
     * @var CustomerGroupPlugin
     */
    private $model;

    /**
     * @var IndexerInterface|MockObject
     */
    private $indexerMock;

    /**
     * @var CustomerGroupResourceModel|MockObject
     */
    private $subjectMock;

    /**
     * @var ClientOptionsInterface|MockObject
     */
    private $customerOptionsMock;

    /**
     * @var IndexerRegistry|MockObject
     */
    private $indexerRegistryMock;

    protected function setUp(): void
    {
        $this->subjectMock = $this->createMock(CustomerGroupResourceModel::class);
        $this->customerOptionsMock = $this->createMock(
            ClientOptionsInterface::class
        );
        $this->indexerMock = $this->getMockForAbstractClass(
            IndexerInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['getId', 'getState', '__wakeup']
        );
        $this->indexerRegistryMock = $this->createPartialMock(
            IndexerRegistry::class,
            ['get']
        );
        $this->model = new CustomerGroupPlugin(
            $this->indexerRegistryMock,
            $this->customerOptionsMock
        );
    }

    /**
     * @param bool $isObjectNew
     * @param bool $isTaxClassIdChanged
     * @param int $invalidateCounter
     * @return void
     * @dataProvider aroundSaveDataProvider
     */
    public function testAroundSave(
        bool $isObjectNew,
        bool $isTaxClassIdChanged,
        int $invalidateCounter
    ): void {
        $groupMock = $this->createPartialMock(
            CustomerGroupModel::class,
            ['dataHasChangedFor', 'isObjectNew', '__wakeup']
        );
        $groupMock->expects($this->any())->method('isObjectNew')->willReturn($isObjectNew);
        $groupMock->expects($this->any())
            ->method('dataHasChangedFor')
            ->with('tax_class_id')
            ->willReturn($isTaxClassIdChanged);

        $closureMock = function (CustomerGroupModel $object) use ($groupMock) {
            $this->assertEquals($object, $groupMock);
            return $this->subjectMock;
        };

        $this->indexerMock->expects($this->exactly($invalidateCounter))->method('invalidate');
        $this->indexerRegistryMock->expects($this->exactly($invalidateCounter))
            ->method('get')
            ->with(FulltextIndexer::INDEXER_ID)
            ->willReturn($this->indexerMock);

        $this->assertEquals(
            $this->subjectMock,
            $this->model->aroundSave($this->subjectMock, $closureMock, $groupMock)
        );
    }

    /**
     * Data Provider for testAroundSave
     *
     * @return array
     */
    public static function aroundSaveDataProvider(): array
    {
        return [
            [false, false, 0],
            [false, true, 1],
            [true, false, 1],
            [true, true, 1],
        ];
    }
}

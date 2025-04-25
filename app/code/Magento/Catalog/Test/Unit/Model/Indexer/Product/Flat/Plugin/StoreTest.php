<?php
/**
 * Copyright 2024 Adobe
 * All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Indexer\Product\Flat\Plugin;

use Magento\Catalog\Model\Indexer\Product\Flat\Plugin\Store as StorePlugin;
use Magento\Catalog\Model\Indexer\Product\Flat\Processor;
use Magento\Store\Model\ResourceModel\Store as StoreResourceModel;
use Magento\Store\Model\Store;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class StoreTest extends TestCase
{
    /**
     * @var Processor|MockObject
     */
    private $processorMock;

    /**
     * @var Store|MockObject
     */
    private $storeMock;

    /**
     * @var MockObject
     */
    private $subjectMock;

    /**
     * @var StorePlugin
     */
    private $storePlugin;

    protected function setUp(): void
    {
        $this->processorMock = $this->createPartialMock(
            Processor::class,
            ['markIndexerAsInvalid']
        );

        $this->subjectMock = $this->createMock(StoreResourceModel::class);
        $this->storeMock = $this->createPartialMock(
            Store::class,
            ['getId', 'dataHasChangedFor']
        );

        $this->storePlugin = new StorePlugin($this->processorMock);
    }

    /**
     * @param string $matcherMethod
     * @param int|null $storeId
     * @dataProvider storeDataProvider
     */
    public function testAfterSave(string $matcherMethod, ?int $storeId): void
    {
        $this->processorMock->expects($this->{$matcherMethod}())->method('markIndexerAsInvalid');

        $this->storeMock->expects($this->once())->method('getId')->willReturn($storeId);

        $this->assertSame(
            $this->subjectMock,
            $this->storePlugin->afterSave($this->subjectMock, $this->subjectMock, $this->storeMock)
        );
    }

    /**
     * @param string $matcherMethod
     * @param bool $storeGroupChanged
     * @dataProvider storeGroupDataProvider
     */
    public function testAfterSaveSwitchStoreGroup(string $matcherMethod, bool $storeGroupChanged): void
    {
        $this->processorMock->expects($this->{$matcherMethod}())->method('markIndexerAsInvalid');

        $this->storeMock->expects($this->once())->method('getId')->willReturn(1);

        $this->storeMock->expects($this->once())->method('dataHasChangedFor')
            ->with('group_id')->willReturn($storeGroupChanged);

        $this->assertSame(
            $this->subjectMock,
            $this->storePlugin->afterSave($this->subjectMock, $this->subjectMock, $this->storeMock)
        );
    }

    /**
     * @return array
     */
    public static function storeGroupDataProvider(): array
    {
        return [['once', true], ['never', false]];
    }

    /**
     * @return array
     */
    public static function storeDataProvider(): array
    {
        return [['once', null], ['never', 1]];
    }
}

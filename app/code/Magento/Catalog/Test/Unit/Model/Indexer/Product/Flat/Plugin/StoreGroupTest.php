<?php
/**
 * Copyright 2024 Adobe
 * All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Indexer\Product\Flat\Plugin;

use Magento\Catalog\Model\Indexer\Product\Flat\Plugin\StoreGroup;
use Magento\Catalog\Model\Indexer\Product\Flat\Processor;
use Magento\Store\Model\Group as StoreGroupModel;
use Magento\Store\Model\ResourceModel\Group;
use Magento\Store\Model\Store;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class StoreGroupTest extends TestCase
{
    /**
     * @var Processor|MockObject
     */
    private $processorMock;

    /**
     * @var Store|MockObject
     */
    private $storeGroupMock;

    /**
     * @var MockObject
     */
    private $subjectMock;

    /**
     * @var StoreGroup
     */
    private $storeGroupPlugin;

    protected function setUp(): void
    {
        $this->processorMock = $this->createPartialMock(
            Processor::class,
            ['markIndexerAsInvalid']
        );

        $this->subjectMock = $this->createMock(Group::class);
        $this->storeGroupMock = $this->createPartialMock(
            StoreGroupModel::class,
            ['getId', 'dataHasChangedFor']
        );

        $this->storeGroupPlugin = new StoreGroup($this->processorMock);
    }

    /**
     * @param string $matcherMethod
     * @param int|null $storeId
     * @dataProvider storeGroupDataProvider
     */
    public function testAfterSave(string $matcherMethod, ?int $storeId): void
    {
        $this->processorMock->expects($this->{$matcherMethod}())->method('markIndexerAsInvalid');

        $this->storeGroupMock->expects($this->once())->method('getId')->willReturn($storeId);

        $this->assertSame(
            $this->subjectMock,
            $this->storeGroupPlugin->afterSave($this->subjectMock, $this->subjectMock, $this->storeGroupMock)
        );
    }

    /**
     * @param string $matcherMethod
     * @param bool $websiteChanged
     * @dataProvider storeGroupWebsiteDataProvider
     */
    public function testAfterSaveChangedWebsite(string $matcherMethod, bool $websiteChanged): void
    {
        $this->processorMock->expects($this->{$matcherMethod}())->method('markIndexerAsInvalid');

        $this->storeGroupMock->expects($this->once())->method('getId')->willReturn(1);

        $this->storeGroupMock->expects($this->once())->method('dataHasChangedFor')
            ->with('root_category_id')->willReturn($websiteChanged);

        $this->assertSame(
            $this->subjectMock,
            $this->storeGroupPlugin->afterSave($this->subjectMock, $this->subjectMock, $this->storeGroupMock)
        );
    }

    /**
     * @return array
     */
    public static function storeGroupWebsiteDataProvider(): array
    {
        return [['once', true], ['never', false]];
    }

    /**
     * @return array
     */
    public static function storeGroupDataProvider(): array
    {
        return [['once', null], ['never', 1]];
    }
}

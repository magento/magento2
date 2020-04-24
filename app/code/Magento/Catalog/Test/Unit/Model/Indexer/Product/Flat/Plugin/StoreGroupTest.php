<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Indexer\Product\Flat\Plugin;

use Magento\Catalog\Model\Indexer\Product\Flat\Plugin\StoreGroup;
use Magento\Catalog\Model\Indexer\Product\Flat\Processor;
use Magento\Store\Model\ResourceModel\Group;
use Magento\Store\Model\Store;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class StoreGroupTest extends TestCase
{
    /**
     * @var Processor|MockObject
     */
    protected $processorMock;

    /**
     * @var Store|MockObject
     */
    protected $storeGroupMock;

    /**
     * @var MockObject
     */
    protected $subjectMock;

    protected function setUp(): void
    {
        $this->processorMock = $this->createPartialMock(
            Processor::class,
            ['markIndexerAsInvalid']
        );

        $this->subjectMock = $this->createMock(Group::class);
        $this->storeGroupMock = $this->createPartialMock(
            \Magento\Store\Model\Group::class,
            ['getId', 'dataHasChangedFor']
        );
    }

    /**
     * @param string $matcherMethod
     * @param int|null $storeId
     * @dataProvider storeGroupDataProvider
     */
    public function testBeforeSave($matcherMethod, $storeId)
    {
        $this->processorMock->expects($this->{$matcherMethod}())->method('markIndexerAsInvalid');

        $this->storeGroupMock->expects($this->once())->method('getId')->willReturn($storeId);

        $model = new StoreGroup($this->processorMock);
        $model->beforeSave($this->subjectMock, $this->storeGroupMock);
    }

    /**
     * @param string $matcherMethod
     * @param bool $websiteChanged
     * @dataProvider storeGroupWebsiteDataProvider
     */
    public function testChangedWebsiteBeforeSave($matcherMethod, $websiteChanged)
    {
        $this->processorMock->expects($this->{$matcherMethod}())->method('markIndexerAsInvalid');

        $this->storeGroupMock->expects($this->once())->method('getId')->willReturn(1);

        $this->storeGroupMock->expects(
            $this->once()
        )->method(
            'dataHasChangedFor'
        )->with(
            'root_category_id'
        )->willReturn(
            $websiteChanged
        );

        $model = new StoreGroup($this->processorMock);
        $model->beforeSave($this->subjectMock, $this->storeGroupMock);
    }

    /**
     * @return array
     */
    public function storeGroupWebsiteDataProvider()
    {
        return [['once', true], ['never', false]];
    }

    /**
     * @return array
     */
    public function storeGroupDataProvider()
    {
        return [['once', null], ['never', 1]];
    }
}

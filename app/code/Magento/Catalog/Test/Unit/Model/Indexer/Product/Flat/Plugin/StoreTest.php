<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Indexer\Product\Flat\Plugin;

use Magento\Catalog\Model\Indexer\Product\Flat\Processor;
use Magento\Store\Model\Store;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class StoreTest extends TestCase
{
    /**
     * @var Processor|MockObject
     */
    protected $processorMock;

    /**
     * @var Store|MockObject
     */
    protected $storeMock;

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

        $this->subjectMock = $this->createMock(\Magento\Store\Model\ResourceModel\Store::class);
        $this->storeMock = $this->createPartialMock(
            Store::class,
            ['getId', 'dataHasChangedFor']
        );
    }

    /**
     * @param string $matcherMethod
     * @param int|null $storeId
     * @dataProvider storeDataProvider
     */
    public function testBeforeSave($matcherMethod, $storeId)
    {
        $this->processorMock->expects($this->{$matcherMethod}())->method('markIndexerAsInvalid');

        $this->storeMock->expects($this->once())->method('getId')->willReturn($storeId);

        $model = new \Magento\Catalog\Model\Indexer\Product\Flat\Plugin\Store($this->processorMock);
        $model->beforeSave($this->subjectMock, $this->storeMock);
    }

    /**
     * @param string $matcherMethod
     * @param bool $storeGroupChanged
     * @dataProvider storeGroupDataProvider
     */
    public function testBeforeSaveSwitchStoreGroup($matcherMethod, $storeGroupChanged)
    {
        $this->processorMock->expects($this->{$matcherMethod}())->method('markIndexerAsInvalid');

        $this->storeMock->expects($this->once())->method('getId')->willReturn(1);

        $this->storeMock->expects(
            $this->once()
        )->method(
            'dataHasChangedFor'
        )->with(
            'group_id'
        )->willReturn(
            $storeGroupChanged
        );

        $model = new \Magento\Catalog\Model\Indexer\Product\Flat\Plugin\Store($this->processorMock);
        $model->beforeSave($this->subjectMock, $this->storeMock);
    }

    /**
     * @return array
     */
    public function storeGroupDataProvider()
    {
        return [['once', true], ['never', false]];
    }

    /**
     * @return array
     */
    public function storeDataProvider()
    {
        return [['once', null], ['never', 1]];
    }
}

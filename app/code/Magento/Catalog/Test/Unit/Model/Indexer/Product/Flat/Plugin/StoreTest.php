<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\Indexer\Product\Flat\Plugin;

class StoreTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Flat\Processor|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $processorMock;

    /**
     * @var \Magento\Store\Model\Store|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $storeMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $subjectMock;

    protected function setUp(): void
    {
        $this->processorMock = $this->createPartialMock(
            \Magento\Catalog\Model\Indexer\Product\Flat\Processor::class,
            ['markIndexerAsInvalid']
        );

        $this->subjectMock = $this->createMock(\Magento\Store\Model\ResourceModel\Store::class);
        $this->storeMock = $this->createPartialMock(
            \Magento\Store\Model\Store::class,
            ['getId', '__wakeup', 'dataHasChangedFor']
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

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\Indexer\Product\Flat\Plugin;

class StoreGroupTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Flat\Processor|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $processorMock;

    /**
     * @var \Magento\Store\Model\Store|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $storeGroupMock;

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

        $this->subjectMock = $this->createMock(\Magento\Store\Model\ResourceModel\Group::class);
        $this->storeGroupMock = $this->createPartialMock(
            \Magento\Store\Model\Group::class,
            ['getId', '__wakeup', 'dataHasChangedFor']
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

        $model = new \Magento\Catalog\Model\Indexer\Product\Flat\Plugin\StoreGroup($this->processorMock);
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

        $model = new \Magento\Catalog\Model\Indexer\Product\Flat\Plugin\StoreGroup($this->processorMock);
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

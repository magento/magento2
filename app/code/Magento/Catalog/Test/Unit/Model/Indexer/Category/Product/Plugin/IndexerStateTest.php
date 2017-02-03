<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\Indexer\Category\Product\Plugin;

class IndexerStateTest extends \PHPUnit_Framework_TestCase
{
    public function testAfterSave()
    {
        $testableIndex = \Magento\Catalog\Model\Indexer\Product\Category::INDEXER_ID;
        $changedIndex = \Magento\Catalog\Model\Indexer\Category\Product::INDEXER_ID;
        $testableStatus = 'testable_status';

        $testableState = $this->getMockBuilder(
            'Magento\Indexer\Model\Indexer\State'
        )->setMethods(
            ['getIndexerId', 'getStatus', '__wakeup']
        )->disableOriginalConstructor()->getMock();

        $testableState->expects($this->exactly(2))->method('getIndexerId')->will($this->returnValue($testableIndex));

        $testableState->expects($this->exactly(2))->method('getStatus')->will($this->returnValue($testableStatus));

        $state = $this->getMockBuilder(
            'Magento\Indexer\Model\Indexer\State'
        )->setMethods(
            ['setData', 'loadByIndexer', 'save', '__wakeup']
        )->disableOriginalConstructor()->getMock();

        $state->expects($this->once())->method('loadByIndexer')->with($changedIndex)->will($this->returnSelf());

        $state->expects($this->once())->method('save')->will($this->returnSelf());

        $state->expects(
            $this->at(1)
        )->method(
            'setData'
        )->with(
            $this->logicalOr($this->equalTo('status'), $this->equalTo($testableStatus))
        )->will(
            $this->returnSelf()
        );

        $model = new \Magento\Catalog\Model\Indexer\Category\Product\Plugin\IndexerState($state);
        $this->assertInstanceOf('\Magento\Indexer\Model\Indexer\State', $model->afterSave($testableState));
    }
}

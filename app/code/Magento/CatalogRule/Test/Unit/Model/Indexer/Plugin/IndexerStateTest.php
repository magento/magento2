<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogRule\Test\Unit\Model\Indexer\Plugin;

use Magento\CatalogRule\Model\Indexer\Plugin\IndexerState;
use Magento\CatalogRule\Model\Indexer\Product\ProductRuleProcessor;
use Magento\CatalogRule\Model\Indexer\Rule\RuleProductProcessor;
use Magento\Indexer\Model\Indexer\State;

class IndexerStateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var State|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $stateMock;

    /**
     * @var string
     */
    protected $testableStatus = 'testable_status';

    public function setUp()
    {
        $this->stateMock = $this->getMockBuilder(
            State::class
        )->setMethods(
            ['setData', 'loadByIndexer', 'save', '__wakeup']
        )->disableOriginalConstructor()->getMock();
    }

    public function testAfterSave()
    {
        $testableIndex = RuleProductProcessor::INDEXER_ID;
        $changedIndex = ProductRuleProcessor::INDEXER_ID;

        /** @var State|\PHPUnit_Framework_MockObject_MockObject $testableState */
        $testableState = $this->getMockBuilder(
            State::class
        )->setMethods(
            ['getIndexerId', 'getStatus', '__wakeup']
        )->disableOriginalConstructor()
            ->getMock();

        $testableState->expects($this->exactly(2))
            ->method('getIndexerId')
            ->will($this->returnValue($testableIndex));

        $testableState->expects($this->exactly(2))
            ->method('getStatus')
            ->will($this->returnValue($this->testableStatus));

        $this->stateMock->expects($this->once())
            ->method('loadByIndexer')
            ->with($changedIndex)
            ->will($this->returnSelf());

        $this->stateMock->expects($this->once())
            ->method('save')
            ->will($this->returnSelf());

        $this->stateMock->expects($this->at(1))
            ->method('setData')
            ->with($this->logicalOr($this->equalTo('status'), $this->equalTo($this->testableStatus)))
            ->will($this->returnSelf());

        $model = new IndexerState($this->stateMock);
        $this->assertInstanceOf(State::class, $model->afterSave($testableState));
    }
}

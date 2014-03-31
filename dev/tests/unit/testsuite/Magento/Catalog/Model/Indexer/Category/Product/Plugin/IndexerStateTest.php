<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Catalog\Model\Indexer\Category\Product\Plugin;

class IndexerStateTest extends \PHPUnit_Framework_TestCase
{
    public function testAfterSetStatus()
    {
        $testableIndex = \Magento\Catalog\Model\Indexer\Product\Category::INDEXER_ID;
        $changedIndex = \Magento\Catalog\Model\Indexer\Category\Product::INDEXER_ID;
        $testableStatus = 'testable_status';

        $testableState = $this->getMockBuilder(
            'Magento\Indexer\Model\Indexer\State'
        )->setMethods(
            array('getIndexerId', 'getStatus', '__wakeup')
        )->disableOriginalConstructor()->getMock();

        $testableState->expects($this->exactly(2))->method('getIndexerId')->will($this->returnValue($testableIndex));

        $testableState->expects($this->once())->method('getStatus')->will($this->returnValue($testableStatus));

        $state = $this->getMockBuilder(
            'Magento\Indexer\Model\Indexer\State'
        )->setMethods(
            array('setData', 'loadByIndexer', 'save', '__wakeup')
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
        $this->assertInstanceOf('\Magento\Indexer\Model\Indexer\State', $model->afterSetStatus($testableState));
    }
}

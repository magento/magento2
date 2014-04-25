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
namespace Magento\Indexer\Model\Indexer;

class CollectionTest extends \PHPUnit_Framework_TestCase
{
    public function testLoadData()
    {
        $indexerIdOne = 'first_indexer_id';
        $indexerIdSecond = 'second_indexer_id';

        $entityFactory = $this->getMockBuilder(
            'Magento\Framework\Data\Collection\EntityFactoryInterface'
        )->disableOriginalConstructor()->setMethods(
            array('create')
        )->getMock();

        $config = $this->getMockBuilder('Magento\Indexer\Model\ConfigInterface')->getMock();

        $statesFactory = $this->getMockBuilder(
            'Magento\Indexer\Model\Resource\Indexer\State\CollectionFactory'
        )->disableOriginalConstructor()->setMethods(
            array('create')
        )->getMock();

        $states = $this->getMockBuilder(
            'Magento\Indexer\Model\Resource\Indexer\State\Collection'
        )->disableOriginalConstructor()->getMock();

        $state = $this->getMockBuilder(
            'Magento\Indexer\Model\Indexer\State'
        )->setMethods(
            array('getIndexerId', '__wakeup')
        )->disableOriginalConstructor()->getMock();

        $state->expects($this->any())->method('getIndexerId')->will($this->returnValue('second_indexer_id'));

        $indexer = $this->getMockBuilder(
            'Magento\Indexer\Model\Indexer\Collection'
        )->setMethods(
            array('load', 'setState')
        )->disableOriginalConstructor()->getMock();

        $indexer->expects($this->once())->method('setState')->with($state);

        $indexer->expects($this->any())->method('load')->with($this->logicalOr($indexerIdOne, $indexerIdSecond));

        $entityFactory->expects(
            $this->any()
        )->method(
            'create'
        )->with(
            'Magento\Indexer\Model\IndexerInterface'
        )->will(
            $this->returnValue($indexer)
        );

        $statesFactory->expects($this->once())->method('create')->will($this->returnValue($states));

        $config->expects(
            $this->once()
        )->method(
            'getIndexers'
        )->will(
            $this->returnValue(array($indexerIdOne => 1, $indexerIdSecond => 2))
        );

        $states->expects($this->any())->method('getItems')->will($this->returnValue(array($state)));

        $collection = new \Magento\Indexer\Model\Indexer\Collection($entityFactory, $config, $statesFactory);
        $this->assertInstanceOf('Magento\Indexer\Model\Indexer\Collection', $collection->loadData());
    }
}

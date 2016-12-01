<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Mview\Test\Unit\View;

class CollectionTest extends \PHPUnit_Framework_TestCase
{
    public function testLoadDataAndGetViewsByStateMode()
    {
        $indexerIdOne = 'first_indexer_id';
        $indexerIdSecond = 'second_indexer_id';

        $entityFactory = $this->getMockBuilder(
            \Magento\Framework\Data\Collection\EntityFactoryInterface::class
        )->disableOriginalConstructor()->setMethods(
            ['create']
        )->getMock();

        $config = $this->getMockBuilder(\Magento\Framework\Mview\ConfigInterface::class)->getMock();

        $statesFactory = $this->getMockBuilder(
            \Magento\Framework\Mview\View\State\CollectionFactory::class
        )->disableOriginalConstructor()->setMethods(
            ['create']
        )->getMock();

        $states = $this->getMockBuilder(
            \Magento\Framework\Mview\View\State\Collection::class
        )->setMethods(
            ['getItems']
        )->disableOriginalConstructor()->getMock();

        $state = $this->getMockForAbstractClass(
            \Magento\Framework\Mview\View\StateInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['getViewId', 'getMode', '__wakeup']
        );

        $state->expects($this->any())->method('getViewId')->will($this->returnValue('second_indexer_id'));

        $state->expects(
            $this->any()
        )->method(
            'getMode'
        )->will(
            $this->returnValue(\Magento\Framework\Mview\View\StateInterface::MODE_DISABLED)
        );

        $view = $this->getMockForAbstractClass(
            \Magento\Framework\Mview\ViewInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['load', 'setState', 'getState', '__wakeup']
        );

        $view->expects($this->once())->method('setState')->with($state);
        $view->expects($this->any())->method('getState')->will($this->returnValue($state));
        $view->expects($this->any())->method('load')->with($this->logicalOr($indexerIdOne, $indexerIdSecond));

        $entityFactory->expects(
            $this->any()
        )->method(
            'create'
        )->with(
            \Magento\Framework\Mview\ViewInterface::class
        )->will(
            $this->returnValue($view)
        );

        $statesFactory->expects($this->once())->method('create')->will($this->returnValue($states));

        $config->expects(
            $this->once()
        )->method(
            'getViews'
        )->will(
            $this->returnValue([$indexerIdOne => 1, $indexerIdSecond => 2])
        );

        $states->expects($this->any())->method('getItems')->will($this->returnValue([$state]));

        $collection = new \Magento\Framework\Mview\View\Collection($entityFactory, $config, $statesFactory);
        $this->assertInstanceOf(\Magento\Framework\Mview\View\Collection::class, $collection->loadData());

        $views = $collection->getViewsByStateMode(\Magento\Framework\Mview\View\StateInterface::MODE_DISABLED);
        $this->assertCount(2, $views);
        $this->assertInstanceOf(\Magento\Framework\Mview\ViewInterface::class, $views[0]);
        $this->assertInstanceOf(\Magento\Framework\Mview\ViewInterface::class, $views[1]);

        $views = $collection->getViewsByStateMode(\Magento\Framework\Mview\View\StateInterface::MODE_ENABLED);
        $this->assertCount(0, $views);
    }
}

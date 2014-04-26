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
namespace Magento\Framework\Mview\View;

class CollectionTest extends \PHPUnit_Framework_TestCase
{
    public function testLoadDataAndGetViewsByStateMode()
    {
        $indexerIdOne = 'first_indexer_id';
        $indexerIdSecond = 'second_indexer_id';

        $entityFactory = $this->getMockBuilder(
            'Magento\Framework\Data\Collection\EntityFactoryInterface'
        )->disableOriginalConstructor()->setMethods(
            array('create')
        )->getMock();

        $config = $this->getMockBuilder('Magento\Framework\Mview\ConfigInterface')->getMock();

        $statesFactory = $this->getMockBuilder(
            'Magento\Framework\Mview\View\State\CollectionFactory'
        )->disableOriginalConstructor()->setMethods(
            array('create')
        )->getMock();

        $states = $this->getMockBuilder(
            'Magento\Framework\Mview\View\State\Collection'
        )->setMethods(
            array('getItems')
        )->disableOriginalConstructor()->getMock();

        $state = $this->getMockForAbstractClass(
            'Magento\Framework\Mview\View\StateInterface', [], '', false, false, true,
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
            'Magento\Framework\Mview\ViewInterface', [], '', false, false, true,
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
            'Magento\Framework\Mview\ViewInterface'
        )->will(
            $this->returnValue($view)
        );

        $statesFactory->expects($this->once())->method('create')->will($this->returnValue($states));

        $config->expects(
            $this->once()
        )->method(
            'getViews'
        )->will(
            $this->returnValue(array($indexerIdOne => 1, $indexerIdSecond => 2))
        );

        $states->expects($this->any())->method('getItems')->will($this->returnValue(array($state)));

        $collection = new \Magento\Framework\Mview\View\Collection($entityFactory, $config, $statesFactory);
        $this->assertInstanceOf('Magento\Framework\Mview\View\Collection', $collection->loadData());

        $views = $collection->getViewsByStateMode(\Magento\Framework\Mview\View\StateInterface::MODE_DISABLED);
        $this->assertCount(2, $views);
        $this->assertInstanceOf('Magento\Framework\Mview\ViewInterface', $views[0]);
        $this->assertInstanceOf('Magento\Framework\Mview\ViewInterface', $views[1]);

        $views = $collection->getViewsByStateMode(\Magento\Framework\Mview\View\StateInterface::MODE_ENABLED);
        $this->assertCount(0, $views);
    }
}

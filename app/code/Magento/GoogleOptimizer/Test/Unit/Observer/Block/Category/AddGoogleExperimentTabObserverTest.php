<?php
/**
 * Google Optimizer Observer Category Tab
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GoogleOptimizer\Test\Unit\Observer\Block\Category;

use Magento\GoogleOptimizer\Block\Adminhtml\Catalog\Category\Edit\Tab\Googleoptimizer;
use Magento\GoogleOptimizer\Helper\Data;
use Magento\Framework\View\Layout;
use Magento\Catalog\Block\Adminhtml\Category\Tabs;
use Magento\Framework\Event\Observer;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\GoogleOptimizer\Observer\Block\Category\AddGoogleExperimentTabObserver;
use Magento\Framework\View\Element\BlockInterface;
use Magento\Framework\Event;
use Magento\Catalog\Model\Category;

class AddGoogleExperimentTabObserverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $helperMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $layoutMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $tabsMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $eventObserverMock;

    /**
     * @var \Magento\GoogleOptimizer\Observer\Block\Category\AddGoogleExperimentTabObserver
     */
    private $modelObserver;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $categoryMock;

    protected function setUp()
    {
        $this->helperMock = $this->getMock(Data::class, [], [], '', false);
        $this->layoutMock = $this->getMock(Layout::class, [], [], '', false);
        $this->tabsMock = $this->getMock(Tabs::class, [], [], '', false);
        $this->eventObserverMock = $this->getMock(Observer::class, [], [], '', false);
        $this->categoryMock = $this->getMock(Category::class, [], [], '', false);
        $objectManagerHelper = new ObjectManager($this);
        $this->modelObserver = $objectManagerHelper->getObject(
            AddGoogleExperimentTabObserver::class,
            ['helper' => $this->helperMock, 'layout' => $this->layoutMock]
        );
    }

    public function testAddGoogleExperimentTabSuccess()
    {
        $this->helperMock->expects($this->once())->method('isGoogleExperimentActive')->will($this->returnValue(true));
        $block = $this->getMock(BlockInterface::class, [], [], '', false);
        $block->expects($this->once())->method('toHtml')->will($this->returnValue('generated html'));
        $this->layoutMock->expects($this->once())->method('createBlock')
            ->with(
                Googleoptimizer::class,
                'google-experiment-form'
            )
            ->will($this->returnValue($block));
        $event = $this->getMock(Event::class, ['getTabs'], [], '', false);
        $event->expects($this->any())->method('getTabs')->will($this->returnValue($this->tabsMock));
        $this->eventObserverMock->expects($this->any())->method('getEvent')->will($this->returnValue($event));
        $this->tabsMock->expects($this->once())->method('addTab')
            ->with(
                'google-experiment-tab',
                ['label' => __('Category View Optimization'), 'content' => 'generated html']
            );
        $this->categoryMock->expects($this->any())->method('getStoreId')->will($this->returnValue(1));
        $this->tabsMock->expects($this->any())->method('getCategory')->will($this->returnValue($this->categoryMock));
        $this->modelObserver->execute($this->eventObserverMock);
    }

    public function testAddGoogleExperimentTabFail()
    {
        $this->categoryMock->expects($this->any())->method('getStoreId')->will($this->returnValue(1));
        $this->tabsMock->expects($this->any())->method('getCategory')
            ->will($this->returnValue($this->categoryMock));
        $this->helperMock->expects($this->once())->method('isGoogleExperimentActive')
            ->will($this->returnValue(false));
        $this->layoutMock->expects($this->never())->method('createBlock');
        $this->tabsMock->expects($this->never())->method('addTab');
        $event = $this->getMock(Event::class, ['getTabs'], [], '', false);
        $this->eventObserverMock->expects($this->once())->method('getEvent')->will($this->returnValue($event));
        $event->expects($this->any())->method('getTabs')->will($this->returnValue($this->tabsMock));
        $this->modelObserver->execute($this->eventObserverMock);
    }
}

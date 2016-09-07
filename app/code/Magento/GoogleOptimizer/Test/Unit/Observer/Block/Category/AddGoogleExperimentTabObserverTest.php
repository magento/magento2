<?php
/**
 * Google Optimizer Observer Category Tab
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GoogleOptimizer\Test\Unit\Observer\Block\Category;
use \Magento\GoogleOptimizer\Block\Adminhtml\Catalog\Category\Edit\Tab\Googleoptimizer;

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
        $this->helperMock = $this->getMock(\Magento\GoogleOptimizer\Helper\Data::class, [], [], '', false);
        $this->layoutMock = $this->getMock(\Magento\Framework\View\Layout::class, [], [], '', false);
        $this->tabsMock = $this->getMock(\Magento\Catalog\Block\Adminhtml\Category\Tabs::class, [], [], '', false);
        $this->eventObserverMock = $this->getMock(\Magento\Framework\Event\Observer::class, [], [], '', false);
        $this->categoryMock = $this->getMock(\Magento\Catalog\Model\Category::class, ['getStoreId'], [], '', false);
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->modelObserver = $objectManagerHelper->getObject(
            \Magento\GoogleOptimizer\Observer\Block\Category\AddGoogleExperimentTabObserver::class,
            ['helper' => $this->helperMock, 'layout' => $this->layoutMock]
        );
    }

    public function testAddGoogleExperimentTabSuccess()
    {
        $this->helperMock->expects($this->once())->method('isGoogleExperimentActive')->will($this->returnValue(true));
        $block = $this->getMock(\Magento\Framework\View\Element\BlockInterface::class, [], [], '', false);
        $block->expects($this->once())->method('toHtml')->will($this->returnValue('generated html'));
        $this->layoutMock->expects($this->once())->method('createBlock')
            ->with(
                Googleoptimizer::class,
                'google-experiment-form'
            )
            ->will($this->returnValue($block));
        $event = $this->getMock(\Magento\Framework\Event::class, ['getTabs'], [], '', false);
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
        $event = $this->getMock('Magento\Framework\Event', ['getTabs'], [], '', false);
        $this->eventObserverMock->expects($this->once())->method('getEvent')->will($this->returnValue($event));
        $event->expects($this->any())->method('getTabs')->will($this->returnValue($this->tabsMock));
        $this->modelObserver->execute($this->eventObserverMock);
    }
}

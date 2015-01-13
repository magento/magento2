<?php
/**
 * Google Optimizer Observer Category Tab
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GoogleOptimizer\Model\Observer\Block\Category;

class TabTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_helperMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_layoutMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_tabsMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_eventObserverMock;

    /**
     * @var \Magento\GoogleOptimizer\Model\Observer\Block\Category\Tab
     */
    protected $_modelObserver;

    /**
     * @var \Magento\GoogleOptimizer\Model\Observer\Block\Category\Tab
     */
    protected $_observer;

    protected function setUp()
    {
        $this->_helperMock = $this->getMock('Magento\GoogleOptimizer\Helper\Data', [], [], '', false);
        $this->_layoutMock = $this->getMock('Magento\Framework\View\Layout', [], [], '', false);
        $this->_tabsMock = $this->getMock(
            'Magento\Catalog\Block\Adminhtml\Category\Tabs',
            [],
            [],
            '',
            false
        );
        $this->_eventObserverMock = $this->getMock('Magento\Framework\Event\Observer', [], [], '', false);

        $objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_modelObserver = $objectManagerHelper->getObject(
            'Magento\GoogleOptimizer\Model\Observer\Block\Category\Tab',
            ['helper' => $this->_helperMock, 'layout' => $this->_layoutMock]
        );
    }

    public function testAddGoogleExperimentTabSuccess()
    {
        $this->_helperMock->expects($this->once())->method('isGoogleExperimentActive')->will($this->returnValue(true));

        $block = $this->getMock('Magento\Framework\View\Element\BlockInterface', [], [], '', false);
        $block->expects($this->once())->method('toHtml')->will($this->returnValue('generated html'));

        $this->_layoutMock->expects(
            $this->once()
        )->method(
            'createBlock'
        )->with(
            'Magento\GoogleOptimizer\Block\Adminhtml\Catalog\Category\Edit\Tab\Googleoptimizer',
            'google-experiment-form'
        )->will(
            $this->returnValue($block)
        );

        $event = $this->getMock('Magento\Framework\Event', ['getTabs'], [], '', false);
        $event->expects($this->once())->method('getTabs')->will($this->returnValue($this->_tabsMock));
        $this->_eventObserverMock->expects($this->once())->method('getEvent')->will($this->returnValue($event));

        $this->_tabsMock->expects(
            $this->once()
        )->method(
            'addTab'
        )->with(
            'google-experiment-tab',
            ['label' => __('Category View Optimization'), 'content' => 'generated html']
        );

        $this->_modelObserver->addGoogleExperimentTab($this->_eventObserverMock);
    }

    public function testAddGoogleExperimentTabFail()
    {
        $this->_helperMock->expects(
            $this->once()
        )->method(
            'isGoogleExperimentActive'
        )->will(
            $this->returnValue(false)
        );
        $this->_layoutMock->expects($this->never())->method('createBlock');
        $this->_tabsMock->expects($this->never())->method('addTab');
        $this->_eventObserverMock->expects($this->never())->method('getEvent');

        $this->_modelObserver->addGoogleExperimentTab($this->_eventObserverMock);
    }
}

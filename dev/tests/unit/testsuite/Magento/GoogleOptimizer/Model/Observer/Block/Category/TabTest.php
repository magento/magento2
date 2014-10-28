<?php
/**
 * Google Optimizer Observer Category Tab
 *
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
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
        $this->_helperMock = $this->getMock('Magento\GoogleOptimizer\Helper\Data', array(), array(), '', false);
        $this->_layoutMock = $this->getMock('Magento\Framework\View\Layout', array(), array(), '', false);
        $this->_tabsMock = $this->getMock(
            'Magento\Catalog\Block\Adminhtml\Category\Tabs',
            array(),
            array(),
            '',
            false
        );
        $this->_eventObserverMock = $this->getMock('Magento\Framework\Event\Observer', array(), array(), '', false);

        $objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_modelObserver = $objectManagerHelper->getObject(
            'Magento\GoogleOptimizer\Model\Observer\Block\Category\Tab',
            array('helper' => $this->_helperMock, 'layout' => $this->_layoutMock)
        );
    }

    public function testAddGoogleExperimentTabSuccess()
    {
        $this->_helperMock->expects($this->once())->method('isGoogleExperimentActive')->will($this->returnValue(true));

        $block = $this->getMock('Magento\Framework\View\Element\BlockInterface', array(), array(), '', false);
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

        $event = $this->getMock('Magento\Framework\Event', array('getTabs'), array(), '', false);
        $event->expects($this->once())->method('getTabs')->will($this->returnValue($this->_tabsMock));
        $this->_eventObserverMock->expects($this->once())->method('getEvent')->will($this->returnValue($event));

        $this->_tabsMock->expects(
            $this->once()
        )->method(
            'addTab'
        )->with(
            'google-experiment-tab',
            array('label' => __('Category View Optimization'), 'content' => 'generated html')
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

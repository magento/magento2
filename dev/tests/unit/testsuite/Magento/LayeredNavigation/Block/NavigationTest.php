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

namespace Magento\LayeredNavigation\Block;

class NavigationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $catalogLayerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $filterListMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $layoutMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $visibilityFlagMock;

    /**
     * @var \Magento\LayeredNavigation\Block\Navigation
     */
    protected $model;

    protected function setUp()
    {
        $this->catalogLayerMock = $this->getMock('\Magento\Catalog\Model\Layer', array(), array(), '', false);
        $this->filterListMock = $this->getMock('\Magento\Catalog\Model\Layer\FilterList', array(), array(), '', false);
        $this->visibilityFlagMock = $this->getMock(
            '\Magento\Catalog\Model\Layer\AvailabilityFlagInterface',
            array(),
            array(),
            '',
            false
        );

        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->model = $objectManager->getObject(
            '\Magento\LayeredNavigation\Block\Navigation',
            array(
                'catalogLayer' => $this->catalogLayerMock,
                'filterList' => $this->filterListMock,
                'visibilityFlag' => $this->visibilityFlagMock
            )
        );
        $this->layoutMock = $this->getMock('\Magento\Framework\View\LayoutInterface', array(), array(), '', false);
    }

    public function testGetStateHtml()
    {
        $stateHtml = 'I feel good';
        $this->filterListMock->expects($this->any())->method('getFilters')->will($this->returnValue(array()));
        $this->layoutMock->expects($this->at(0))->method('getChildName')
            ->with(null, 'renderer');
        $this->layoutMock->expects($this->at(1))->method('getChildName')
            ->with(null, 'state')
            ->will($this->returnValue('state block'));

        $this->layoutMock->expects($this->once())->method('renderElement')
            ->with('state block', true)
            ->will($this->returnValue($stateHtml));

        $this->model->setLayout($this->layoutMock);
        $this->assertEquals($stateHtml, $this->model->getStateHtml());
    }

    /**
     * @covers \Magento\LayeredNavigation\Block\Navigation::getLayer()
     * @covers \Magento\LayeredNavigation\Block\Navigation::getFilters()
     * @covers \Magento\LayeredNavigation\Block\Navigation::canShowBlock()
     */
    public function testCanShowBlock()
    {
        // getFilers()
        $filters = array('To' => 'be', 'or' => 'not', 'to' => 'be');

        $this->filterListMock->expects($this->exactly(2))->method('getFilters')
            ->with($this->catalogLayerMock)
            ->will($this->returnValue($filters));
        $this->assertEquals($filters, $this->model->getFilters());

        // canShowBlock()
        $enabled = true;
        $this->visibilityFlagMock
            ->expects($this->once())
            ->method('isEnabled')
            ->with($this->catalogLayerMock, $filters)
            ->will($this->returnValue($enabled));
        $this->assertEquals($enabled, $this->model->canShowBlock());
    }

    public function testGetClearUrl()
    {
        $this->filterListMock->expects($this->any())->method('getFilters')->will($this->returnValue(array()));
        $this->model->setLayout($this->layoutMock);
        $this->layoutMock->expects($this->once())->method('getChildName')->will($this->returnValue('sample block'));

        $blockMock = $this->getMockForAbstractClass(
            '\Magento\Framework\View\Element\AbstractBlock',
            array(),
            '',
            false
        );
        $clearUrl = 'very clear URL';
        $blockMock->setClearUrl($clearUrl);

        $this->layoutMock->expects($this->once())->method('getBlock')->will($this->returnValue($blockMock));
        $this->assertEquals($clearUrl, $this->model->getClearUrl());
    }
}

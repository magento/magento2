<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Block\Adminhtml\Order;

/**
 * Class GridTest
 */
class GridTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $componentFactory;

    /**
     * @var \Magento\Sales\Block\Adminhtml\Order\Grid
     */
    protected $block;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextMock;

    protected function setUp()
    {
        $this->componentFactory = $this->getMockBuilder('Magento\Framework\View\Element\UiComponentFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->requestMock = $this->getMockBuilder('Magento\Framework\App\RequestInterface')
            ->disableOriginalConstructor()
            ->setMethods(['has'])
            ->getMockForAbstractClass();
        $this->contextMock = $this->getMockBuilder('Magento\Backend\Block\Template\Context')
            ->disableOriginalConstructor()
            ->getMock();
        $this->contextMock->expects($this->any())
            ->method('getRequest')
            ->willReturn($this->requestMock);
        $arguments = [
            'componentFactory' => $this->componentFactory,
            'context' => $this->contextMock
        ];
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        /** @var \Magento\Sales\Block\Adminhtml\Order\Grid $block */
        $this->block = $helper->getObject('Magento\Sales\Block\Adminhtml\Order\Grid', $arguments);
    }

    public function testPrepareCollection()
    {
        $contextMock = $this->getMockBuilder('Magento\Framework\View\Element\UiComponent\ContextInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $collectionMock = $this->getMockBuilder('Magento\Sales\Model\Resource\Order\Grid\Collection')
            ->disableOriginalConstructor()
            ->getMock();
        $providerName = 'Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider';
        $dataProviderMock = $this->getMockBuilder($providerName)
            ->disableOriginalConstructor()
            ->getMock();
        $componentMock = $this->getMockBuilder('Magento\Framework\View\Element\UiComponentInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $childComponentMock = $this->getMockBuilder('Magento\Framework\View\Element\UiComponentInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->componentFactory->expects($this->once())
            ->method('create')
            ->with('sales_order_grid')
            ->willReturn($componentMock);
        $componentMock->expects($this->once())
            ->method('getChildComponents')
            ->willReturn([$childComponentMock]);
        $childComponentMock->expects($this->once())
            ->method('getChildComponents')
            ->willReturn([]);
        $childComponentMock->expects($this->once())
            ->method('prepare');
        $componentMock->expects($this->once())
            ->method('render');
        $componentMock->expects($this->once())
            ->method('getContext')
            ->willReturn($contextMock);
        $contextMock->expects($this->once())
            ->method('getDataProvider')
            ->willReturn($dataProviderMock);
        $dataProviderMock->expects($this->once())
            ->method('getCollection')
            ->willReturn($collectionMock);
        $this->requestMock->expects($this->any())
            ->method('has')
            ->withAnyParameters()
            ->willReturn(false);
        $layoutMock = $this->getMockBuilder('Magento\Framework\View\LayoutInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $blockMock = $this->getMockBuilder('Magento\Framework\View\Element\AbstractBlock')
            ->disableOriginalConstructor()
            ->setMethods(['getLayout'])
            ->getMockForAbstractClass();
        $blockMock->expects($this->any())
            ->method('getLayout')
            ->willReturn($layoutMock);

        $layoutMock->expects($this->any())
            ->method('getBlock')
            ->willReturn($blockMock);
        $layoutMock->expects($this->any())
            ->method('getChildName')
            ->willReturn($blockMock);
        $this->block->setData('id', 1);
        $this->block->setLayout($layoutMock);
        $this->assertInstanceOf(
            'Magento\Sales\Model\Resource\Order\Grid\Collection',
            $this->block->getPreparedCollection()
        );
    }
}

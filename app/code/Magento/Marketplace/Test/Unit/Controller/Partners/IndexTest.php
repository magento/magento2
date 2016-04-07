<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Marketplace\Test\Unit\Controller\Partners;

class IndexTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Marketplace\Controller\Adminhtml\Partners\Index
     */
    private $partnersControllerMock;

    protected function setUp()
    {
        $this->partnersControllerMock = $this->getControllerIndexMock(
            [
                'getRequest',
                'getResponse',
                'getLayoutFactory'
            ]
        );
    }

    /**
     * @covers \Magento\Marketplace\Controller\Adminhtml\Partners\Index::execute
     */
    public function testExecute()
    {
        $requestMock = $this->getRequestMock(['isAjax']);
        $requestMock->expects($this->once())
            ->method('isAjax')
            ->will($this->returnValue(true));

        $this->partnersControllerMock->expects($this->once())
            ->method('getRequest')
            ->will($this->returnValue($requestMock));

        $layoutMock = $this->getLayoutMock();
        $blockMock = $this->getBlockInterfaceMock();
        $blockMock->expects($this->once())
            ->method('toHtml')
            ->will($this->returnValue(''));

        $layoutMock->expects($this->once())
            ->method('createBlock')
            ->will($this->returnValue($blockMock));

        $layoutMockFactory = $this->getLayoutFactoryMock(['create']);
        $layoutMockFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue($layoutMock));

        $this->partnersControllerMock->expects($this->once())
            ->method('getLayoutFactory')
            ->will($this->returnValue($layoutMockFactory));

        $responseMock = $this->getResponseMock(['appendBody']);
        $responseMock->expects($this->once())
            ->method('appendBody')
            ->will($this->returnValue(''));
        $this->partnersControllerMock->expects($this->once())
            ->method('getResponse')
            ->will($this->returnValue($responseMock));

        $this->partnersControllerMock->execute();
    }

    /**
     * Gets partners controller mock
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|\Magento\Marketplace\Controller\Adminhtml\Partners\Index
     */
    public function getControllerIndexMock($methods = null)
    {
        return $this->getMock('Magento\Marketplace\Controller\Adminhtml\Partners\Index', $methods, [], '', false);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\View\LayoutFactory
     */
    public function getLayoutFactoryMock($methods = null)
    {
        return $this->getMock('Magento\Framework\View\LayoutFactory', $methods, [], '', false);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\View\LayoutInterface
     */
    public function getLayoutMock()
    {
        return $this->getMockForAbstractClass('Magento\Framework\View\LayoutInterface');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\HTTP\PhpEnvironment\Response
     */
    public function getResponseMock($methods = null)
    {
        return $this->getMock('Magento\Framework\HTTP\PhpEnvironment\Response', $methods, [], '', false);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\Request\Http
     */
    public function getRequestMock($methods = null)
    {
        return $this->getMock('Magento\Framework\App\Request\Http', $methods, [], '', false);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\View\Element\BlockInterface
     */
    public function getBlockInterfaceMock()
    {
        return $this->getMockForAbstractClass('Magento\Framework\View\Element\BlockInterface');
    }
}

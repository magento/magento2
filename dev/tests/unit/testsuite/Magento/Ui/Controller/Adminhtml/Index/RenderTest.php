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
namespace Magento\Ui\Controller\Adminhtml\Index;

/**
 * Class RenderTest
 */
class RenderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Render
     */
    protected $render;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $responseMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $uiFactoryMock;

    public function setUp()
    {
        $this->requestMock = $this->getMockBuilder('Magento\Framework\App\Request\Http')
            ->disableOriginalConstructor()
            ->getMock();
        $this->responseMock = $this->getMockBuilder('Magento\Framework\App\Response\Http')
            ->disableOriginalConstructor()
            ->getMock();
        $contextMock = $this->getMockBuilder('Magento\Backend\App\Action\Context')
            ->disableOriginalConstructor()
            ->getMock();
        $contextMock->expects($this->any())
            ->method('getRequest')
            ->willReturn($this->requestMock);
        $contextMock->expects($this->any())
            ->method('getResponse')
            ->willReturn($this->responseMock);

        $this->uiFactoryMock = $this->getMockBuilder('Magento\Framework\View\Element\UiComponentFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->render = new Render($contextMock, $this->uiFactoryMock);
    }

    public function testExecute()
    {
        $name = 'test-name';
        $renderedData = '<html>data</html>';

        $this->requestMock->expects($this->at(0))
            ->method('getParam')
            ->with('component')
            ->willReturn($name);
        $this->requestMock->expects($this->at(1))
            ->method('getParam')
            ->with('name')
            ->willReturn($name);
        $this->responseMock->expects($this->once())
            ->method('appendBody')
            ->with($renderedData);

        $viewMock = $this->getMock('Magento\Ui\Form\Field', ['render'], [], '', false);
        $viewMock->expects($this->once())
            ->method('render')
            ->willReturn($renderedData);
        $this->uiFactoryMock->expects($this->once())
            ->method('createUiComponent')
            ->willReturn($viewMock);

        $this->assertNull($this->render->execute());
    }
}

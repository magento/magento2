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

namespace Magento\Framework\View\Result;

/**
 * Class LayoutTest
 * @covers \Magento\Framework\View\Result\Layout
 */
class LayoutTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers \Magento\Framework\View\Result\Layout::getLayout()
     */
    public function testGetLayout()
    {
        /** @var \Magento\Framework\View\LayoutInterface $layout */
        $layout = $this->getMock('Magento\Framework\View\LayoutInterface', [], [], '', false);

        $context = $this->getMock('Magento\Framework\View\Element\Template\Context', [], [], '', false);
        $context->expects($this->once())->method('getLayout')->will($this->returnValue($layout));

        /** @var \Magento\Framework\View\Result\Layout $resultLayout */
        $resultLayout = (new \Magento\TestFramework\Helper\ObjectManager($this))
            ->getObject('Magento\Framework\View\Result\Layout', ['context' => $context]);
        $this->assertSame($layout, $resultLayout->getLayout());
    }

    /**
     * @covers \Magento\Framework\View\Result\Layout::initLayout()
     */
    public function testInitLayout()
    {
        /** @var \Magento\Framework\View\Result\Layout $resultLayout */
        $resultLayout = (new \Magento\TestFramework\Helper\ObjectManager($this))
            ->getObject('Magento\Framework\View\Result\Layout');
        $this->assertSame($resultLayout, $resultLayout->initLayout());
    }

    public function testGetDefaultLayoutHandle()
    {
        /** @var \Magento\Framework\App\Request\Http|\PHPUnit_Framework_MockObject_MockObject $request */
        $request = $this->getMock('Magento\Framework\App\Request\Http', [], [], '', false);
        $request->expects($this->once())->method('getFullActionName')
            ->will($this->returnValue('Module_Controller_Action'));

        /** @var \Magento\Framework\View\Element\Template\Context|\PHPUnit_Framework_MockObject_MockObject $request */
        $context = $this->getMock('Magento\Framework\View\Element\Template\Context', [], [], '', false);
        $context->expects($this->once())->method('getRequest')->will($this->returnValue($request));

        /** @var \Magento\Framework\View\Result\Layout $resultLayout */
        $resultLayout = (new \Magento\TestFramework\Helper\ObjectManager($this))
            ->getObject('Magento\Framework\View\Result\Layout', ['context' => $context]);
        $this->assertEquals('module_controller_action', $resultLayout->getDefaultLayoutHandle());
    }

    public function testAddHandle()
    {
        $processor = $this->getMock('Magento\Framework\View\Layout\ProcessorInterface', [], [], '', false);
        $processor->expects($this->once())->method('addHandle')->with('module_controller_action');

        /** @var \Magento\Framework\View\LayoutInterface|\PHPUnit_Framework_MockObject_MockObject $layout */
        $layout = $this->getMock('Magento\Framework\View\LayoutInterface', [], [], '', false);
        $layout->expects($this->once())->method('getUpdate')->will($this->returnValue($processor));

        /** @var \Magento\Framework\View\Element\Template\Context|\PHPUnit_Framework_MockObject_MockObject $request */
        $context = $this->getMock('Magento\Framework\View\Element\Template\Context', [], [], '', false);
        $context->expects($this->once())->method('getLayout')->will($this->returnValue($layout));

        /** @var \Magento\Framework\View\Result\Layout $resultLayout */
        $resultLayout = (new \Magento\TestFramework\Helper\ObjectManager($this))
            ->getObject('Magento\Framework\View\Result\Layout', ['context' => $context]);
        $this->assertSame($resultLayout, $resultLayout->addHandle('module_controller_action'));
    }

    public function testAddUpdate()
    {
        $processor = $this->getMock('Magento\Framework\View\Layout\ProcessorInterface', [], [], '', false);
        $processor->expects($this->once())->method('addUpdate')->with('handle_name');

        /** @var \Magento\Framework\View\LayoutInterface|\PHPUnit_Framework_MockObject_MockObject $layout */
        $layout = $this->getMock('Magento\Framework\View\LayoutInterface', [], [], '', false);
        $layout->expects($this->once())->method('getUpdate')->will($this->returnValue($processor));

        /** @var \Magento\Framework\View\Element\Template\Context|\PHPUnit_Framework_MockObject_MockObject $request */
        $context = $this->getMock('Magento\Framework\View\Element\Template\Context', [], [], '', false);
        $context->expects($this->once())->method('getLayout')->will($this->returnValue($layout));

        /** @var \Magento\Framework\View\Result\Layout $resultLayout */
        $resultLayout = (new \Magento\TestFramework\Helper\ObjectManager($this))
            ->getObject('Magento\Framework\View\Result\Layout', ['context' => $context]);
        $resultLayout->addUpdate('handle_name');
    }

    /**
     * @param int|string $httpCode
     * @param string $headerName
     * @param string $headerValue
     * @param bool $replaceHeader
     * @param \PHPUnit_Framework_MockObject_Matcher_InvokedCount $setHttpResponseCodeCount
     * @param \PHPUnit_Framework_MockObject_Matcher_InvokedCount $setHeaderCount
     * @dataProvider providerRenderResult
     */
    public function testRenderResult(
        $httpCode, $headerName, $headerValue, $replaceHeader, $setHttpResponseCodeCount, $setHeaderCount
    ) {
        /** @var \Magento\Framework\View\LayoutInterface|\PHPUnit_Framework_MockObject_MockObject $layout */
        $layout = $this->getMock('Magento\Framework\View\LayoutInterface', [], [], '', false);
        $layout->expects($this->once())->method('getOutput')->will($this->returnValue('output'));

        /** @var \Magento\Framework\App\Request\Http|\PHPUnit_Framework_MockObject_MockObject $request */
        $request = $this->getMock('Magento\Framework\App\Request\Http', [], [], '', false);
        $request->expects($this->once())->method('getFullActionName')
            ->will($this->returnValue('Module_Controller_Action'));

        $eventManager = $this->getMock('Magento\Framework\Event\ManagerInterface', [], [], '', false);
        $eventManager->expects($this->exactly(2))->method('dispatch')->withConsecutive(
            ['controller_action_layout_render_before'],
            ['controller_action_layout_render_before_Module_Controller_Action']
        );

        /** @var \Magento\Framework\View\Element\Template\Context|\PHPUnit_Framework_MockObject_MockObject $request */
        $context = $this->getMock('Magento\Framework\View\Element\Template\Context', [], [], '', false);
        $context->expects($this->once())->method('getLayout')->will($this->returnValue($layout));
        $context->expects($this->once())->method('getRequest')->will($this->returnValue($request));
        $context->expects($this->once())->method('getEventManager')->will($this->returnValue($eventManager));

        /** @var \Magento\Framework\App\Response\Http|\PHPUnit_Framework_MockObject_MockObject $response */
        $response = $this->getMock('Magento\Framework\App\Response\Http', [], [], '', false);
        $response->expects($setHttpResponseCodeCount)->method('setHttpResponseCode')->with($httpCode);
        $response->expects($setHeaderCount)->method('setHeader')->with($headerName, $headerValue, $replaceHeader);
        $response->expects($this->once())->method('appendBody')->with('output');

        /** @var \Magento\Framework\View\Result\Layout $resultLayout */
        $resultLayout = (new \Magento\TestFramework\Helper\ObjectManager($this))
            ->getObject('Magento\Framework\View\Result\Layout', ['context' => $context]);
        $resultLayout->setHttpResponseCode($httpCode);

        if ($headerName && $headerValue) {
            $resultLayout->setHeader($headerName, $headerValue, $replaceHeader);
        }

        $resultLayout->renderResult($response);
    }

    /**
     * @return array
     */
    public function providerRenderResult()
    {
        return [
            [200, 'content-type', 'text/html', true, $this->once(), $this->once()],
            [0, '', '', false, $this->never(), $this->never()]
        ];
    }

    public function testAddDefaultHandle()
    {
        $processor = $this->getMock('Magento\Framework\View\Layout\ProcessorInterface', [], [], '', false);
        $processor->expects($this->once())->method('addHandle')->with('module_controller_action');

        /** @var \Magento\Framework\View\LayoutInterface|\PHPUnit_Framework_MockObject_MockObject $layout */
        $layout = $this->getMock('Magento\Framework\View\LayoutInterface', [], [], '', false);
        $layout->expects($this->once())->method('getUpdate')->will($this->returnValue($processor));

        /** @var \Magento\Framework\App\Request\Http|\PHPUnit_Framework_MockObject_MockObject $request */
        $request = $this->getMock('Magento\Framework\App\Request\Http', [], [], '', false);
        $request->expects($this->once())->method('getFullActionName')
            ->will($this->returnValue('Module_Controller_Action'));

        /** @var \Magento\Framework\View\Element\Template\Context|\PHPUnit_Framework_MockObject_MockObject $request */
        $context = $this->getMock('Magento\Framework\View\Element\Template\Context', [], [], '', false);
        $context->expects($this->once())->method('getRequest')->will($this->returnValue($request));
        $context->expects($this->once())->method('getLayout')->will($this->returnValue($layout));

        /** @var \Magento\Framework\View\Result\Layout $resultLayout */
        $resultLayout = (new \Magento\TestFramework\Helper\ObjectManager($this))
            ->getObject('Magento\Framework\View\Result\Layout', ['context' => $context]);
        $this->assertSame($resultLayout, $resultLayout->addDefaultHandle());
    }
}

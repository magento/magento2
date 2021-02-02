<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Test\Unit\Result;

/**
 * Class LayoutTest
 * @covers \Magento\Framework\View\Result\Layout
 */
class LayoutTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\App\Request\Http|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $request;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Framework\View\Layout
     */
    protected $layout;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Framework\Translate\InlineInterface
     */
    protected $translateInline;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Framework\View\Result\Layout
     */
    protected $resultLayout;

    protected function setUp(): void
    {
        $this->layout = $this->createMock(\Magento\Framework\View\Layout::class);
        $this->request = $this->createMock(\Magento\Framework\App\Request\Http::class);
        $this->eventManager = $this->createMock(\Magento\Framework\Event\ManagerInterface::class);
        $this->translateInline = $this->createMock(\Magento\Framework\Translate\InlineInterface::class);

        $context = $this->createMock(\Magento\Framework\View\Element\Template\Context::class);
        $context->expects($this->any())->method('getLayout')->willReturn($this->layout);
        $context->expects($this->any())->method('getRequest')->willReturn($this->request);
        $context->expects($this->any())->method('getEventManager')->willReturn($this->eventManager);

        $this->resultLayout = (new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this))
            ->getObject(
                \Magento\Framework\View\Result\Layout::class,
                ['context' => $context, 'translateInline' => $this->translateInline]
            );
    }

    /**
     * @covers \Magento\Framework\View\Result\Layout::getLayout()
     */
    public function testGetLayout()
    {
        $this->assertSame($this->layout, $this->resultLayout->getLayout());
    }

    public function testGetDefaultLayoutHandle()
    {
        $this->request->expects($this->once())
            ->method('getFullActionName')
            ->willReturn('Module_Controller_Action');

        $this->assertEquals('module_controller_action', $this->resultLayout->getDefaultLayoutHandle());
    }

    public function testAddHandle()
    {
        $processor = $this->createMock(\Magento\Framework\View\Layout\ProcessorInterface::class);
        $processor->expects($this->once())->method('addHandle')->with('module_controller_action');

        $this->layout->expects($this->once())->method('getUpdate')->willReturn($processor);

        $this->assertSame($this->resultLayout, $this->resultLayout->addHandle('module_controller_action'));
    }

    public function testAddUpdate()
    {
        $processor = $this->createMock(\Magento\Framework\View\Layout\ProcessorInterface::class);
        $processor->expects($this->once())->method('addUpdate')->with('handle_name');

        $this->layout->expects($this->once())->method('getUpdate')->willReturn($processor);

        $this->resultLayout->addUpdate('handle_name');
    }

    /**
     * @param int|string $httpCode
     * @param string $headerName
     * @param string $headerValue
     * @param bool $replaceHeader
     * @param \PHPUnit\Framework\MockObject\Matcher\InvokedCount $setHttpResponseCodeCount
     * @param \PHPUnit\Framework\MockObject\Matcher\InvokedCount $setHeaderCount
     * @dataProvider renderResultDataProvider
     */
    public function testRenderResult(
        $httpCode,
        $headerName,
        $headerValue,
        $replaceHeader,
        $setHttpResponseCodeCount,
        $setHeaderCount
    ) {
        $layoutOutput = 'output';

        $this->layout->expects($this->once())->method('getOutput')->willReturn($layoutOutput);

        $this->request->expects($this->once())->method('getFullActionName')
            ->willReturn('Module_Controller_Action');

        $this->eventManager->expects($this->exactly(2))->method('dispatch')->withConsecutive(
            ['layout_render_before'],
            ['layout_render_before_Module_Controller_Action']
        );

        $this->translateInline->expects($this->once())
            ->method('processResponseBody')
            ->with($layoutOutput)
            ->willReturnSelf();

        /** @var \Magento\Framework\App\Response\Http|\PHPUnit\Framework\MockObject\MockObject $response */
        $response = $this->createMock(\Magento\Framework\App\Response\Http::class);
        $response->expects($setHttpResponseCodeCount)->method('setHttpResponseCode')->with($httpCode);
        $response->expects($setHeaderCount)->method('setHeader')->with($headerName, $headerValue, $replaceHeader);
        $response->expects($this->once())->method('appendBody')->with($layoutOutput);

        $this->resultLayout->setHttpResponseCode($httpCode);

        if ($headerName && $headerValue) {
            $this->resultLayout->setHeader($headerName, $headerValue, $replaceHeader);
        }

        $this->resultLayout->renderResult($response);
    }

    /**
     * @return array
     */
    public function renderResultDataProvider()
    {
        return [
            [200, 'content-type', 'text/html', true, $this->once(), $this->once()],
            [0, '', '', false, $this->never(), $this->never()]
        ];
    }

    public function testAddDefaultHandle()
    {
        $processor = $this->createMock(\Magento\Framework\View\Layout\ProcessorInterface::class);
        $processor->expects($this->once())->method('addHandle')->with('module_controller_action');

        $this->layout->expects($this->once())->method('getUpdate')->willReturn($processor);

        $this->request->expects($this->once())->method('getFullActionName')
            ->willReturn('Module_Controller_Action');

        $this->assertSame($this->resultLayout, $this->resultLayout->addDefaultHandle());
    }
}

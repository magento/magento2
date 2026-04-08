<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\View\Test\Unit\Result;

use Magento\Framework\App\Request\Http;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\Translate\InlineInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\Layout;
use Magento\Framework\View\Layout\ProcessorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Rule\InvokedCount;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @covers \Magento\Framework\View\Result\Layout
 */
class LayoutTest extends TestCase
{
    use MockCreationTrait;
    /**
     * @var Http|MockObject
     */
    protected $request;

    /**
     * @var MockObject|ManagerInterface
     */
    protected $eventManager;

    /**
     * @var MockObject|Layout
     */
    protected $layout;

    /**
     * @var MockObject|InlineInterface
     */
    protected $translateInline;

    /**
     * @var MockObject|\Magento\Framework\View\Result\Layout
     */
    protected $resultLayout;

    protected function setUp(): void
    {
        $this->layout = $this->createMock(Layout::class);
        $this->request = $this->createMock(Http::class);
        $this->eventManager = $this->createMock(ManagerInterface::class);
        $this->translateInline = $this->createMock(InlineInterface::class);

        $context = $this->createMock(Context::class);
        $context->expects($this->any())->method('getLayout')->willReturn($this->layout);
        $context->expects($this->any())->method('getRequest')->willReturn($this->request);
        $context->expects($this->any())->method('getEventManager')->willReturn($this->eventManager);

        $this->resultLayout = (new ObjectManager($this))
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
        $processor = $this->createMock(ProcessorInterface::class);
        $processor->expects($this->once())->method('addHandle')->with('module_controller_action');

        $this->layout->expects($this->once())->method('getUpdate')->willReturn($processor);

        $this->assertSame($this->resultLayout, $this->resultLayout->addHandle('module_controller_action'));
    }

    public function testAddUpdate()
    {
        $processor = $this->createMock(ProcessorInterface::class);
        $processor->expects($this->once())->method('addUpdate')->with('handle_name');

        $this->layout->expects($this->once())->method('getUpdate')->willReturn($processor);

        $this->resultLayout->addUpdate('handle_name');
    }

    /**
     * @param int|string $httpCode
     * @param string $headerName
     * @param string $headerValue
     * @param bool $replaceHeader
     * @param InvokedCount $setHttpResponseCodeCount
     * @param InvokedCount $setHeaderCount     */
    #[DataProvider('renderResultDataProvider')]
    public function testRenderResult(
        $httpCode,
        $headerName,
        $headerValue,
        $replaceHeader,
        $setHttpResponseCodeCount,
        $setHeaderCount
    ) {
        // Convert string expectations to matchers
        $setHttpResponseCodeCount = is_string($setHttpResponseCodeCount) 
            ? $this->createInvocationMatcher($setHttpResponseCodeCount) 
            : $setHttpResponseCodeCount;
        $setHeaderCount = is_string($setHeaderCount) 
            ? $this->createInvocationMatcher($setHeaderCount) 
            : $setHeaderCount;
        
        $layoutOutput = 'output';

        $this->layout->expects($this->once())->method('getOutput')->willReturn($layoutOutput);

        $this->request->expects($this->once())->method('getFullActionName')
            ->willReturn('Module_Controller_Action');

        $this->eventManager->expects($this->exactly(2))->method('dispatch')
            ->willReturnCallback(
                function ($arg) {
                    if ($arg == 'layout_render_before' || $arg == 'layout_render_before_Module_Controller_Action') {
                        return null;
                    }
                }
            );

        $this->translateInline->expects($this->once())
            ->method('processResponseBody')
            ->with($layoutOutput)
            ->willReturnSelf();

        /** @var \Magento\Framework\App\Response\Http|MockObject $response */
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
    public static function renderResultDataProvider()
    {
        return [
            [200, 'content-type', 'text/html', true, 'once', 'once'],
            [0, '', '', false, 'never', 'never']
        ];
    }

    public function testAddDefaultHandle()
    {
        $processor = $this->createMock(ProcessorInterface::class);
        $processor->expects($this->once())->method('addHandle')->with('module_controller_action');

        $this->layout->expects($this->once())->method('getUpdate')->willReturn($processor);

        $this->request->expects($this->once())->method('getFullActionName')
            ->willReturn('Module_Controller_Action');

        $this->assertSame($this->resultLayout, $this->resultLayout->addDefaultHandle());
    }
}

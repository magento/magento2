<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\PageCache\Test\Unit\Controller\Block;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\View;
use Magento\Framework\Serialize\Serializer\Base64Json;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Translate\InlineInterface;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\Layout;
use Magento\Framework\View\Layout\LayoutCacheKeyInterface;
use Magento\PageCache\Controller\Block;
use Magento\PageCache\Controller\Block\Esi;
use Magento\PageCache\Test\Unit\Block\Controller\StubBlock;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class EsiTest extends TestCase
{
    /**
     * @var Http|MockObject
     */
    protected $requestMock;

    /**
     * @var \Magento\Framework\App\Response\Http|MockObject
     */
    protected $responseMock;

    /**
     * @var View|MockObject
     */
    protected $viewMock;

    /**
     * @var Block
     */
    protected $action;

    /**
     * @var Layout|MockObject
     */
    protected $layoutMock;

    /**
     * @var LayoutCacheKeyInterface|MockObject
     */
    protected $layoutCacheKeyMock;

    /**
     * @var MockObject|InlineInterface
     */
    protected $translateInline;

    /**
     * Set up before test
     */
    protected function setUp(): void
    {
        $this->layoutMock = $this->getMockBuilder(Layout::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->layoutCacheKeyMock = $this->getMockForAbstractClass(
            LayoutCacheKeyInterface::class
        );

        $contextMock =
            $this->getMockBuilder(Context::class)
                ->disableOriginalConstructor()
                ->getMock();

        $this->requestMock = $this->getMockBuilder(Http::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->responseMock = $this->getMockBuilder(\Magento\Framework\App\Response\Http::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->viewMock = $this->getMockBuilder(View::class)
            ->disableOriginalConstructor()
            ->getMock();

        $contextMock->expects($this->any())->method('getRequest')->willReturn($this->requestMock);
        $contextMock->expects($this->any())->method('getResponse')->willReturn($this->responseMock);
        $contextMock->expects($this->any())->method('getView')->willReturn($this->viewMock);

        $this->translateInline = $this->getMockForAbstractClass(InlineInterface::class);

        $helperObjectManager = new ObjectManager($this);
        $this->action = $helperObjectManager->getObject(
            Esi::class,
            [
                'context' => $contextMock,
                'translateInline' => $this->translateInline,
                'jsonSerializer' => new Json(),
                'base64jsonSerializer' => new Base64Json(),
                'layoutCacheKey' => $this->layoutCacheKeyMock
            ]
        );
    }

    /**
     * @dataProvider executeDataProvider
     * @param string $blockClass
     * @param bool $shouldSetHeaders
     */
    public function testExecute($blockClass, $shouldSetHeaders)
    {
        $block = 'block';
        $handles = ['handle1', 'handle2'];
        $html = 'some-html';
        $mapData = [['blocks', '', json_encode([$block])], ['handles', '', base64_encode(json_encode($handles))]];

        $blockInstance1 = $this->getMockBuilder($blockClass)
            ->disableOriginalConstructor()
            ->onlyMethods(['toHtml'])
            ->getMock();

        $blockInstance1->expects($this->once())->method('toHtml')->willReturn($html);
        $blockInstance1->setTtl(360);

        $this->requestMock->expects($this->any())->method('getParam')->willReturnMap($mapData);

        $this->viewMock->expects($this->once())->method('loadLayout')->with($handles);

        $this->viewMock->expects($this->once())->method('getLayout')->willReturn($this->layoutMock);

        $this->layoutMock->expects($this->never())
            ->method('getUpdate');
        $this->layoutCacheKeyMock->expects($this->atLeastOnce())
            ->method('addCacheKeys');

        $this->layoutMock->expects($this->once())
            ->method('getBlock')
            ->with($block)
            ->willReturn($blockInstance1);

        if ($shouldSetHeaders) {
            $this->responseMock->expects($this->once())
                ->method('setHeader')
                ->with('X-Magento-Tags', implode(',', $blockInstance1->getIdentities()));
        } else {
            $this->responseMock->expects($this->never())
                ->method('setHeader');
        }

        $this->translateInline->expects($this->once())
            ->method('processResponseBody')
            ->with($html)
            ->willReturnSelf();

        $this->responseMock->expects($this->once())
            ->method('appendBody')
            ->with($html);

        $this->action->execute();
    }

    /**
     * @return array
     */
    public function executeDataProvider()
    {
        return [
            [StubBlock::class, true],
            [AbstractBlock::class, false],
        ];
    }

    public function testExecuteBlockNotExists()
    {
        $handles = json_encode(['handle1', 'handle2']);
        $mapData = [
            ['blocks', '', null],
            ['handles', '', $handles],
        ];

        $this->requestMock->expects($this->any())->method('getParam')->willReturnMap($mapData);
        $this->viewMock->expects($this->never())->method('getLayout')->willReturn($this->layoutMock);

        $this->action->execute();
    }
}

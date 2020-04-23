<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\PageCache\Test\Unit\Observer;

use Magento\Framework\DataObject;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Framework\Serialize\Serializer\Base64Json;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\EntitySpecificHandlesList;
use Magento\Framework\View\Layout;
use Magento\PageCache\Model\Config;
use Magento\PageCache\Observer\ProcessLayoutRenderElement;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ProcessLayoutRenderElementTest extends TestCase
{
    /** @var ProcessLayoutRenderElement */
    private $_model;

    /** @var MockObject|EntitySpecificHandlesList */
    private $entitySpecificHandlesListMock;

    /** @var MockObject|Config */
    private $_configMock;

    /** @var MockObject|AbstractBlock */
    private $_blockMock;

    /** @var MockObject|Layout */
    private $_layoutMock;

    /** @var MockObject|Observer */
    private $_observerMock;

    /** @var DataObject */
    private $_transport;

    /**
     * Set up all mocks and data for test
     */
    protected function setUp(): void
    {
        $this->_configMock = $this->createPartialMock(Config::class, ['getType', 'isEnabled']);
        $this->entitySpecificHandlesListMock = $this->createMock(EntitySpecificHandlesList::class);

        $this->_model = new ProcessLayoutRenderElement(
            $this->_configMock,
            $this->entitySpecificHandlesListMock,
            new Json(),
            new Base64Json()
        );
        $this->_observerMock = $this->createPartialMock(Observer::class, ['getEvent']);
        $this->_layoutMock = $this->getMockBuilder(Layout::class)
            ->addMethods(['getHandles'])
            ->onlyMethods(['isCacheable', 'getBlock', 'getUpdate'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->_blockMock = $this->getMockForAbstractClass(
            AbstractBlock::class,
            [],
            '',
            false,
            true,
            true,
            ['getData', 'isScopePrivate', 'getNameInLayout', 'getUrl']
        );
        $this->_transport = new DataObject(['output' => 'test output html']);
    }

    /**
     * @param bool $cacheState
     * @param bool $varnishIsEnabled
     * @param bool $scopeIsPrivate
     * @param int|null $blockTtl
     * @param string $expectedOutput
     * @dataProvider processLayoutRenderDataProvider
     */
    public function testExecute(
        $cacheState,
        $varnishIsEnabled,
        $scopeIsPrivate,
        $blockTtl,
        $expectedOutput
    ) {
        $eventMock = $this->getMockBuilder(Event::class)
            ->addMethods(['getLayout', 'getElementName', 'getTransport'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->_observerMock->expects($this->once())->method('getEvent')->willReturn($eventMock);
        $eventMock->expects($this->once())->method('getLayout')->willReturn($this->_layoutMock);
        $this->_configMock->expects($this->any())->method('isEnabled')->willReturn($cacheState);

        if ($cacheState) {
            $eventMock->expects($this->once())
                ->method('getElementName')
                ->willReturn('blockName');

            $eventMock->expects($this->once())
                ->method('getTransport')
                ->willReturn($this->_transport);

            $this->_layoutMock->expects($this->once())
                ->method('isCacheable')
                ->willReturn(true);

            $this->_layoutMock->expects($this->any())
                ->method('getUpdate')->willReturnSelf();

            $this->_layoutMock->expects($this->any())
                ->method('getHandles')
                ->willReturn(['default', 'catalog_product_view', 'catalog_product_view_id_1']);

            $this->entitySpecificHandlesListMock->expects($this->any())
                ->method('getHandles')
                ->willReturn(['catalog_product_view_id_1']);

            $this->_layoutMock->expects($this->once())
                ->method('getBlock')
                ->willReturn($this->_blockMock);

            if ($varnishIsEnabled) {
                $this->_blockMock->expects($this->once())
                    ->method('getData')
                    ->with('ttl')
                    ->willReturn($blockTtl);
                $this->_blockMock->expects($this->any())
                    ->method('getUrl')
                    ->with(
                        'page_cache/block/esi',
                        ['blocks' => '[null]',
                            'handles' => 'WyJkZWZhdWx0IiwiY2F0YWxvZ19wcm9kdWN0X3ZpZXciXQ==']
                    )
                    ->willReturn(
                        'page_cache/block/wrapesi/with/handles/WyJkZWZhdWx0IiwiY2F0YWxvZ19wcm9kdWN0X3ZpZXciXQ=='
                    );
            }
            if ($scopeIsPrivate) {
                $this->_blockMock->expects($this->once())
                    ->method('getNameInLayout')
                    ->willReturn('testBlockName');
                $this->_blockMock->expects($this->once())
                    ->method('isScopePrivate')
                    ->willReturn($scopeIsPrivate);
            }
            $this->_configMock->expects($this->any())->method('getType')->willReturn($varnishIsEnabled);
        }
        $this->_model->execute($this->_observerMock);

        $this->assertEquals($expectedOutput, $this->_transport['output']);
    }

    public function testExecuteWithBase64Encode()
    {
        $expectedOutput = '<esi:include src="page_cache/block/wrapesi/with/handles/YW5kL290aGVyL3N0dWZm" />';
        $eventMock = $this->getMockBuilder(Event::class)
            ->addMethods(['getLayout', 'getElementName', 'getTransport'])
            ->disableOriginalConstructor()
            ->getMock();
        $expectedUrl = 'page_cache/block/wrapesi/with/handles/' . base64_encode('and/other/stuff');

        $this->_observerMock->expects($this->once())->method('getEvent')->willReturn($eventMock);
        $eventMock->expects($this->once())->method('getLayout')->willReturn($this->_layoutMock);
        $this->_configMock->expects($this->any())->method('isEnabled')->willReturn(true);

        $eventMock->expects($this->once())
            ->method('getElementName')
            ->willReturn('blockName');

        $eventMock->expects($this->once())
            ->method('getTransport')
            ->willReturn($this->_transport);

        $this->_layoutMock->expects($this->once())
            ->method('isCacheable')
            ->willReturn(true);

        $this->_layoutMock->expects($this->any())
            ->method('getUpdate')->willReturnSelf();

        $this->_layoutMock->expects($this->any())
            ->method('getHandles')
            ->willReturn([]);

        $this->_layoutMock->expects($this->once())
            ->method('getBlock')
            ->willReturn($this->_blockMock);

        $this->entitySpecificHandlesListMock->expects($this->any())
            ->method('getHandles')
            ->willReturn(['catalog_product_view_id_1']);

        $this->_blockMock->expects($this->once())
            ->method('getData')
            ->with('ttl')
            ->willReturn(100);
        $this->_blockMock->expects($this->any())
            ->method('getUrl')
            ->willReturn($expectedUrl);

        $this->_blockMock->expects($this->once())
            ->method('getNameInLayout')
            ->willReturn('testBlockName');

        $this->_configMock->expects($this->any())->method('getType')->willReturn(true);

        $this->_model->execute($this->_observerMock);

        $this->assertEquals($expectedOutput, $this->_transport['output']);
    }

    /**
     * Data provider for testProcessLayoutRenderElement
     *
     * @return array
     */
    public function processLayoutRenderDataProvider()
    {
        return [
            'full_page type and Varnish enabled, public scope, ttl is set' => [
                true,
                true,
                false,
                360,
                '<esi:include src="page_cache/block/wrapesi/with/handles/'
                . 'WyJkZWZhdWx0IiwiY2F0YWxvZ19wcm9kdWN0X3ZpZXciXQ==" />',
            ],
            'full_page type and Varnish enabled, public scope, ttl is not set' => [
                true,
                true,
                false,
                null,
                'test output html',
            ],
            'full_page type enabled, Varnish disabled, public scope, ttl is set' => [
                true,
                false,
                false,
                360,
                'test output html',
            ],
            'full_page type enabled, Varnish disabled, public scope, ttl is not set' => [
                true,
                false,
                false,
                null,
                'test output html',
            ],
            'full_page type enabled, Varnish disabled, private scope, ttl is not set' => [
                true,
                false,
                true,
                null,
                '<!-- BLOCK testBlockName -->test output html<!-- /BLOCK testBlockName -->',
            ],
            'full_page type is disabled, Varnish enabled' => [false, true, false, null, 'test output html']
        ];
    }
}

<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\PageCache\Test\Unit\Observer;

use Magento\Framework\View\EntitySpecificHandlesList;

class ProcessLayoutRenderElementTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\PageCache\Observer\ProcessLayoutRenderElement */
    private $_model;

    /** @var \PHPUnit\Framework\MockObject\MockObject|EntitySpecificHandlesList */
    private $entitySpecificHandlesListMock;

    /** @var \PHPUnit\Framework\MockObject\MockObject|\Magento\PageCache\Model\Config */
    private $_configMock;

    /** @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Framework\View\Element\AbstractBlock */
    private $_blockMock;

    /** @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Framework\View\Layout */
    private $_layoutMock;

    /** @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Framework\Event\Observer */
    private $_observerMock;

    /** @var \Magento\Framework\DataObject */
    private $_transport;

    /**
     * Set up all mocks and data for test
     */
    protected function setUp(): void
    {
        $this->_configMock = $this->createPartialMock(\Magento\PageCache\Model\Config::class, ['getType', 'isEnabled']);
        $this->entitySpecificHandlesListMock = $this->createMock(EntitySpecificHandlesList::class);

        $this->_model = new \Magento\PageCache\Observer\ProcessLayoutRenderElement(
            $this->_configMock,
            $this->entitySpecificHandlesListMock,
            new \Magento\Framework\Serialize\Serializer\Json(),
            new \Magento\Framework\Serialize\Serializer\Base64Json()
        );
        $this->_observerMock = $this->createPartialMock(\Magento\Framework\Event\Observer::class, ['getEvent']);
        $this->_layoutMock = $this->createPartialMock(
            \Magento\Framework\View\Layout::class,
            ['isCacheable', 'getBlock', 'getUpdate', 'getHandles']
        );
        $this->_blockMock = $this->getMockForAbstractClass(
            \Magento\Framework\View\Element\AbstractBlock::class,
            [],
            '',
            false,
            true,
            true,
            ['getData', 'isScopePrivate', 'getNameInLayout', 'getUrl']
        );
        $this->_transport = new \Magento\Framework\DataObject(['output' => 'test output html']);
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
        $eventMock = $this->createPartialMock(
            \Magento\Framework\Event::class,
            ['getLayout', 'getElementName', 'getTransport']
        );
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
                ->method('getUpdate')
                ->willReturnSelf();

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
        $eventMock = $this->createPartialMock(
            \Magento\Framework\Event::class,
            ['getLayout', 'getElementName', 'getTransport']
        );
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
                ->method('getUpdate')
                ->willReturnSelf();

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

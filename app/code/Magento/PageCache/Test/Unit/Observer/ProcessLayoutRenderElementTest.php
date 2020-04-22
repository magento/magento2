<?php declare(strict_types=1);
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

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
        $this->_layoutMock = $this->createPartialMock(
            Layout::class,
            ['isCacheable', 'getBlock', 'getUpdate', 'getHandles']
        );
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
        $eventMock = $this->createPartialMock(
            Event::class,
            ['getLayout', 'getElementName', 'getTransport']
        );
        $this->_observerMock->expects($this->once())->method('getEvent')->will($this->returnValue($eventMock));
        $eventMock->expects($this->once())->method('getLayout')->will($this->returnValue($this->_layoutMock));
        $this->_configMock->expects($this->any())->method('isEnabled')->will($this->returnValue($cacheState));

        if ($cacheState) {
            $eventMock->expects($this->once())
                ->method('getElementName')
                ->will($this->returnValue('blockName'));

            $eventMock->expects($this->once())
                ->method('getTransport')
                ->will($this->returnValue($this->_transport));

            $this->_layoutMock->expects($this->once())
                ->method('isCacheable')
                ->will($this->returnValue(true));

            $this->_layoutMock->expects($this->any())
                ->method('getUpdate')
                ->will($this->returnSelf());

            $this->_layoutMock->expects($this->any())
                ->method('getHandles')
                ->will($this->returnValue(['default', 'catalog_product_view', 'catalog_product_view_id_1']));

            $this->entitySpecificHandlesListMock->expects($this->any())
                ->method('getHandles')
                ->will($this->returnValue(['catalog_product_view_id_1']));

            $this->_layoutMock->expects($this->once())
                ->method('getBlock')
                ->will($this->returnValue($this->_blockMock));

            if ($varnishIsEnabled) {
                $this->_blockMock->expects($this->once())
                    ->method('getData')
                    ->with('ttl')
                    ->will($this->returnValue($blockTtl));
                $this->_blockMock->expects($this->any())
                    ->method('getUrl')
                    ->with(
                        'page_cache/block/esi',
                        ['blocks' => '[null]',
                            'handles' => 'WyJkZWZhdWx0IiwiY2F0YWxvZ19wcm9kdWN0X3ZpZXciXQ==']
                    )
                    ->will(
                        $this->returnValue(
                            'page_cache/block/wrapesi/with/handles/WyJkZWZhdWx0IiwiY2F0YWxvZ19wcm9kdWN0X3ZpZXciXQ=='
                        )
                    );
            }
            if ($scopeIsPrivate) {
                $this->_blockMock->expects($this->once())
                    ->method('getNameInLayout')
                    ->will($this->returnValue('testBlockName'));
                $this->_blockMock->expects($this->once())
                    ->method('isScopePrivate')
                    ->will($this->returnValue($scopeIsPrivate));
            }
            $this->_configMock->expects($this->any())->method('getType')->will($this->returnValue($varnishIsEnabled));
        }
        $this->_model->execute($this->_observerMock);

        $this->assertEquals($expectedOutput, $this->_transport['output']);
    }

    public function testExecuteWithBase64Encode()
    {
        $expectedOutput = '<esi:include src="page_cache/block/wrapesi/with/handles/YW5kL290aGVyL3N0dWZm" />';
        $eventMock = $this->createPartialMock(
            Event::class,
            ['getLayout', 'getElementName', 'getTransport']
        );
        $expectedUrl = 'page_cache/block/wrapesi/with/handles/' . base64_encode('and/other/stuff');

        $this->_observerMock->expects($this->once())->method('getEvent')->will($this->returnValue($eventMock));
        $eventMock->expects($this->once())->method('getLayout')->will($this->returnValue($this->_layoutMock));
        $this->_configMock->expects($this->any())->method('isEnabled')->will($this->returnValue(true));

        $eventMock->expects($this->once())
                ->method('getElementName')
                ->will($this->returnValue('blockName'));

        $eventMock->expects($this->once())
                ->method('getTransport')
                ->will($this->returnValue($this->_transport));

        $this->_layoutMock->expects($this->once())
                ->method('isCacheable')
                ->will($this->returnValue(true));

        $this->_layoutMock->expects($this->any())
                ->method('getUpdate')
                ->will($this->returnSelf());

        $this->_layoutMock->expects($this->any())
                ->method('getHandles')
                ->will($this->returnValue([]));

        $this->_layoutMock->expects($this->once())
                ->method('getBlock')
                ->will($this->returnValue($this->_blockMock));

        $this->entitySpecificHandlesListMock->expects($this->any())
            ->method('getHandles')
            ->will($this->returnValue(['catalog_product_view_id_1']));

        $this->_blockMock->expects($this->once())
            ->method('getData')
            ->with('ttl')
            ->will($this->returnValue(100));
        $this->_blockMock->expects($this->any())
            ->method('getUrl')
            ->will($this->returnValue($expectedUrl));

        $this->_blockMock->expects($this->once())
            ->method('getNameInLayout')
            ->will($this->returnValue('testBlockName'));

        $this->_configMock->expects($this->any())->method('getType')->will($this->returnValue(true));

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

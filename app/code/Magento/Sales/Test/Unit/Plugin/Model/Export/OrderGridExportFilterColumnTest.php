<?php
/**
 * Copyright 2022 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Plugin\Model\Export;

use Exception;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Sales\Plugin\Model\Export\OrderGridExportFilterColumn;
use Magento\Sales\Model\ExportViewFilterProcessor;
use Magento\Ui\Model\Export\MetadataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Test case for Process and filter order grid export columns according to view
 */
class OrderGridExportFilterColumnTest extends TestCase
{
    /**
     * @var OrderGridExportFilterColumn
     */
    private $plugin;

    /**
     * @var ExportViewFilterProcessor|MockObject
     */
    private $exportViewFilterProcessorMock;

    /**
     * @var MetadataProvider|MockObject
     */
    private $metadataProviderMock;

    /**
     * @var UiComponentInterface|MockObject
     */
    private $uiComponentInterfaceMock;

    /**
     * @var ContextInterface|MockObject
     */
    private $contextInterfaceMock;

    protected function setUp(): void
    {
        $this->exportViewFilterProcessorMock = $this->createMock(ExportViewFilterProcessor::class);
        $this->metadataProviderMock = $this->createMock(MetadataProvider::class);
        $this->uiComponentInterfaceMock = $this->createMock(UiComponentInterface::class);
        $this->contextInterfaceMock = $this->createMock(ContextInterface::class);
        $this->uiComponentInterfaceMock->expects($this->any())
            ->method('getContext')
            ->willReturn($this->contextInterfaceMock);
        $this->plugin = new OrderGridExportFilterColumn(
            $this->exportViewFilterProcessorMock
        );
    }

    /**
     * Test Plugin which will check getHeaders and update headers according to the custom view
     *
     * @param string $namespace
     * @param array $activeColumns
     * @param array $result     * @throws Exception
     */
    #[DataProvider('getColumnsDataProvider')]
    public function testAfterGetHeaders(string $namespace, array $activeColumns, array $result): void
    {
        $this->contextInterfaceMock->expects($this->any())
            ->method('getNamespace')
            ->willReturn($namespace);
        $this->exportViewFilterProcessorMock->expects($this->any())
            ->method('execute')
            ->willReturn($activeColumns);
        $actualResult = $this->plugin->afterGetHeaders(
            $this->metadataProviderMock,
            $result,
            $this->uiComponentInterfaceMock
        );
        if ($activeColumns) {
            $this->assertEquals(
                $actualResult,
                $activeColumns
            );
        }
    }

    /**
     * Test Plugin which will check getFields and update headers according to the custom view
     *
     * @param string $namespace
     * @param array $activeColumns
     * @param array $result     * @throws Exception
     */
    #[DataProvider('getColumnsDataProvider')]
    public function testAfterGetFields(string $namespace, array $activeColumns, array $result): void
    {
        $this->contextInterfaceMock->expects($this->any())
            ->method('getNamespace')
            ->willReturn($namespace);
        $this->exportViewFilterProcessorMock->expects($this->any())
            ->method('execute')
            ->willReturn($activeColumns);
        $actualResult = $this->plugin->afterGetFields(
            $this->metadataProviderMock,
            $result,
            $this->uiComponentInterfaceMock
        );
        if ($activeColumns) {
            $this->assertEquals(
                $actualResult,
                $activeColumns
            );
        }
    }

    /**
     * DataProvider for Columns Data Provider.
     *
     * @return array
     */
    public static function getColumnsDataProvider(): array
    {
        return [
            'test case when namespace is not `sales_order_grid`' =>
                [
                    'invoice_order_grid',
                    [],
                    [   0 => 'id',
                        2 => 'increment_id',
                        5=> 'invoice_id',
                        7 => 'invoice_details',
                        9 => 'created_date',
                        10 => 'status'
                    ]
                ],
            'test case when namespace is `sales_order_grid`' =>
                [
                    'sales_order_grid',
                    [ 0 => 'id', 2 => 'increment_id', 5=> 'invoice_id', 7 => 'invoice_details'],
                    [   0 => 'id',
                        2 => 'increment_id',
                        5=> 'invoice_id',
                        7 => 'invoice_details',
                        9 => 'created_date',
                        10 => 'status'
                    ]
                ]
        ];
    }
}

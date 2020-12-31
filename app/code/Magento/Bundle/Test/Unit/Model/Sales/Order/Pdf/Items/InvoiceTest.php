<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Test\Unit\Model\Sales\Order\Pdf\Items;

use Magento\Bundle\Model\Sales\Order\Pdf\Items\Invoice;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\DataObject;
use Magento\Framework\Filesystem;
use Magento\Framework\Filter\FilterManager;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Pdf\Invoice as InvoicePdf;
use Magento\Tax\Helper\Data;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Zend_Pdf_Page;

/**
 * Covers bundle order item invoice print logic
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class InvoiceTest extends TestCase
{
    /**
     * @var Invoice|MockObject
     */
    private $model;

    /**
     * @var Data|MockObject
     */
    private $taxDataMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $contextMock = $this->createMock(Context::class);
        $registryMock = $this->createMock(Registry::class);
        $this->taxDataMock = $this->createMock(Data::class);
        $directoryMock = $this->createMock(Filesystem\Directory\Read::class);
        $directoryMock->expects($this->any())->method('getAbsolutePath')->willReturn('');
        $filesystemMock = $this->createMock(Filesystem::class);
        $filesystemMock->expects($this->any())->method('getDirectoryRead')->willReturn($directoryMock);
        $filterManagerMock = $this->createMock(FilterManager::class);
        $stringUtilsMock = $this->createMock(StringUtils::class);
        $stringUtilsMock->expects($this->any())->method('split')->willReturnArgument(0);
        $resourceMock = $this->createMock(AbstractResource::class);
        $collectionMock = $this->createMock(AbstractDb::class);
        $serializerMock = $this->createMock(Json::class);

        $this->model = $this->getMockBuilder(Invoice::class)
            ->setConstructorArgs(
                [
                    $contextMock,
                    $registryMock,
                    $this->taxDataMock,
                    $filesystemMock,
                    $filterManagerMock,
                    $stringUtilsMock,
                    $serializerMock,
                    $resourceMock,
                    $collectionMock,
                    [],
                ]
            )
            ->onlyMethods(
                [
                    '_setFontRegular',
                    'getChildren',
                    'isShipmentSeparately',
                    'isChildCalculated',
                    'getValueHtml',
                    'getSelectionAttributes',
                ]
            )
            ->getMock();
    }

    /**
     * @dataProvider \Magento\Bundle\Test\Unit\Model\Sales\Order\Pdf\Items\InvoiceTestProvider::getData
     * @param array $expected
     * @param string $method
     */
    public function testDrawPrice(array $expected, string $method): void
    {
        $this->taxDataMock->expects($this->any())->method($method)->willReturn(true);
        $pageMock = $this->createMock(Zend_Pdf_Page::class);
        $this->model->setPage($pageMock);
        $pdfMock = $this->createMock(InvoicePdf::class);
        $pdfMock->expects($this->any())->method('drawLineBlocks')->with(
            $pageMock,
            $expected,
            ['table_header' => true]
        )->willReturn($pageMock);
        $this->model->setPdf($pdfMock);

        $this->prepareModel();
        $this->model->draw();
    }

    /**
     * Prepare invoice draw model for test execution
     *
     * @return void
     */
    private function prepareModel(): void
    {
        $parentItem = new DataObject(
            [
                'sku' => 'bundle-simple',
                'name' => 'Bundle',
                'order_item' => new DataObject(
                    [
                        'product_options' => [],
                    ]
                ),
            ]
        );
        $items = [
            new DataObject(
                [
                    'name' => 'Simple1',
                    'sku' => 'simple1',
                    'price' => '10.00',
                    'price_incl_tax' => '10.83',
                    'row_total' => '20.00',
                    'row_total_incl_tax' => '21.66',
                    'qty' => '2',
                    'tax_amount' => '1.66',
                    'order_item' => new DataObject(
                        [
                            'parent_item' => $parentItem,
                        ]
                    ),
                ]
            ),
            new DataObject(
                [
                    'name' => 'Simple2',
                    'sku' => 'simple2',
                    'price' => '5.00',
                    'price_incl_tax' => '5.41',
                    'row_total' => '10.00',
                    'row_total_incl_tax' => '10.83',
                    'qty' => '2',
                    'tax_amount' => '0.83',
                    'order_item' => new DataObject(
                        [
                            'parent_item' => $parentItem,
                        ]
                    ),
                ]
            ),
        ];
        $orderMock = $this->createMock(Order::class);

        $this->model->expects($this->any())->method('getChildren')->willReturn($items);
        $this->model->expects($this->any())->method('isShipmentSeparately')->willReturn(false);
        $this->model->expects($this->any())->method('isChildCalculated')->willReturn(true);
        $this->model->expects($this->at(2))->method('getSelectionAttributes')->willReturn(
            ['option_id' => 1, 'option_label' => 'test option']
        );
        $this->model->expects($this->at(3))->method('getValueHtml')->willReturn($items[0]->getName());
        $this->model->expects($this->at(5))->method('getSelectionAttributes')->willReturn(
            ['option_id' => 1, 'option_label' => 'second option']
        );
        $this->model->expects($this->at(6))->method('getValueHtml')->willReturn($items[1]->getName());

        $orderMock->expects($this->any())->method('formatPriceTxt')->willReturnArgument(0);
        $this->model->setOrder($orderMock);
        $this->model->setItem($parentItem);
    }
}

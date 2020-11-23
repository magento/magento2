<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Downloadable\Test\Unit\Model\Sales\Order\Pdf\Items;

use Magento\Downloadable\Model\Sales\Order\Pdf\Items\Creditmemo;
use Magento\Framework\DataObject;
use Magento\Framework\Filter\FilterManager;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Pdf\AbstractPdf;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CreditmemoTest extends TestCase
{
    /**
     * @var Creditmemo
     */
    private $model;

    /**
     * @var Order|MockObject
     */
    private $order;

    /**
     * @var AbstractPdf|MockObject
     */
    private $pdf;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->order = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->order->expects($this->any())
            ->method('formatPriceTxt')
            ->willReturnCallback([$this, 'formatPrice']);

        $this->pdf = $this->createPartialMock(
            AbstractPdf::class,
            ['drawLineBlocks', 'getPdf']
        );

        $filterManager = $this->getMockBuilder(FilterManager::class)
            ->addMethods(['stripTags'])
            ->disableOriginalConstructor()
            ->getMock();
        $filterManager->expects($this->any())->method('stripTags')->willReturnArgument(0);

        $modelConstructorArgs = $objectManager->getConstructArguments(
            Creditmemo::class,
            ['string' => new StringUtils(), 'filterManager' => $filterManager]
        );

        $this->model = $this->getMockBuilder(Creditmemo::class)
            ->setMethods(['getLinks', 'getLinksTitle'])
            ->setConstructorArgs($modelConstructorArgs)
            ->getMock();

        $this->model->setOrder($this->order);
        $this->model->setPdf($this->pdf);
        $this->model->setPage(new \Zend_Pdf_Page('a4'));
    }

    protected function tearDown(): void
    {
        $this->model = null;
        $this->order = null;
        $this->pdf = null;
    }

    /**
     * Return price formatted as a string including the currency sign
     *
     * @param float $price
     * @return string
     */
    public function formatPrice($price)
    {
        return sprintf('$%.2F', $price);
    }

    public function testDraw()
    {
        $expectedPageSettings = ['table_header' => true];
        $expectedPdfPage = new \Zend_Pdf_Page('a4');
        $expectedPdfData = [
            [
                'lines' => [
                    [
                        ['text' => ['Downloadable Documentation'], 'feed' => 35],
                        ['text' => ['downloadable-docu', 'mentation'], 'feed' => 255, 'align' => 'right'],
                        ['text' => '$20.00', 'feed' => 330, 'font' => 'bold', 'align' => 'right'],
                        ['text' => '$-5.00', 'feed' => 380, 'font' => 'bold', 'align' => 'right'],
                        ['text' => '1', 'feed' => 445, 'font' => 'bold', 'align' => 'right'],
                        ['text' => '$2.00', 'feed' => 495, 'font' => 'bold', 'align' => 'right'],
                        ['text' => '$17.00', 'feed' => 565, 'font' => 'bold', 'align' => 'right'],
                    ],
                    [['text' => ['Test Custom Option'], 'font' => 'italic', 'feed' => 35]],
                    [['text' => ['test value'], 'feed' => 40]],
                    [['text' => ['Download Links'], 'font' => 'italic', 'feed' => 35]],
                    [['text' => ['Magento User Guide'], 'feed' => 40]],
                ],
                'height' => 20,
            ],
        ];

        $this->model->setItem(
            new DataObject(
                [
                    'name' => 'Downloadable Documentation',
                    'sku' => 'downloadable-documentation',
                    'row_total' => 20.00,
                    'discount_amount' => 5.00,
                    'qty' => 1,
                    'tax_amount' => 2.00,
                    'discount_tax_compensation_amount' => 0.00,
                    'order_item' => new DataObject(
                        [
                            'product_options' => [
                                'options' => [['label' => 'Test Custom Option', 'value' => 'test value']],
                            ],
                        ]
                    ),
                ]
            )
        );
        $this->model->expects($this->any())->method('getLinksTitle')->willReturn('Download Links');
        $this->model->expects(
            $this->any()
        )->method(
            'getLinks'
        )->willReturn(
            
                new DataObject(
                    ['purchased_items' => [
                        new DataObject(['link_title' => 'Magento User Guide']), ],
                    ]
                )
            
        );
        $this->pdf->expects(
            $this->once()
        )->method(
            'drawLineBlocks'
        )->with(
            $this->anything(),
            $expectedPdfData,
            $expectedPageSettings
        )->willReturn(
            $expectedPdfPage
        );

        $this->assertNotSame($expectedPdfPage, $this->model->getPage());
        $this->assertNull($this->model->draw());
        $this->assertSame($expectedPdfPage, $this->model->getPage());
    }
}

<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Test\Unit\Model\Sales\Order\Pdf\Items;

class CreditmemoTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Downloadable\Model\Sales\Order\Pdf\Items\Creditmemo
     */
    protected $_model;

    /**
     * @var \Magento\Sales\Model\Order|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_order;

    /**
     * @var \Magento\Sales\Model\Order\Pdf\AbstractPdf|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_pdf;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $arguments = [
            'productFactory' => $this->getMock('Magento\Catalog\Model\ProductFactory', [], [], '', false),
            'orderItemCollectionFactory' => $this->getMock(
                'Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory',
                [],
                [],
                '',
                false
            ),
            'serviceOrderFactory' => $this->getMock(
                'Magento\Sales\Model\Service\OrderFactory',
                [],
                [],
                '',
                false
            ),
            'currencyFactory' => $this->getMock(
                'Magento\Directory\Model\CurrencyFactory',
                [],
                [],
                '',
                false
            ),
            'orderHistoryFactory' => $this->getMock(
                'Magento\Sales\Model\Order\Status\HistoryFactory',
                [],
                [],
                '',
                false
            ),
            'orderTaxCollectionFactory' => $this->getMock(
                'Magento\Tax\Model\ResourceModel\Sales\Order\Tax\CollectionFactory',
                [],
                [],
                '',
                false
            ),
        ];
        $orderConstructorArgs = $objectManager->getConstructArguments('Magento\Sales\Model\Order', $arguments);
        $this->_order = $this->getMock('Magento\Sales\Model\Order', ['formatPriceTxt'], $orderConstructorArgs);
        $this->_order->expects(
            $this->any()
        )->method(
            'formatPriceTxt'
        )->will(
            $this->returnCallback([$this, 'formatPrice'])
        );

        $this->_pdf = $this->getMock(
            'Magento\Sales\Model\Order\Pdf\AbstractPdf',
            ['drawLineBlocks', 'getPdf'],
            [],
            '',
            false,
            false
        );

        $filterManager = $this->getMock(
            'Magento\Framework\Filter\FilterManager',
            ['stripTags'],
            [],
            '',
            false
        );
        $filterManager->expects($this->any())->method('stripTags')->will($this->returnArgument(0));

        $modelConstructorArgs = $objectManager->getConstructArguments(
            'Magento\Downloadable\Model\Sales\Order\Pdf\Items\Creditmemo',
            ['string' => new \Magento\Framework\Stdlib\StringUtils(), 'filterManager' => $filterManager]
        );

        $this->_model = $this->getMock(
            'Magento\Downloadable\Model\Sales\Order\Pdf\Items\Creditmemo',
            ['getLinks', 'getLinksTitle'],
            $modelConstructorArgs
        );

        $this->_model->setOrder($this->_order);
        $this->_model->setPdf($this->_pdf);
        $this->_model->setPage(new \Zend_Pdf_Page('a4'));
    }

    protected function tearDown()
    {
        $this->_model = null;
        $this->_order = null;
        $this->_pdf = null;
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

        $this->_model->setItem(
            new \Magento\Framework\DataObject(
                [
                    'name' => 'Downloadable Documentation',
                    'sku' => 'downloadable-documentation',
                    'row_total' => 20.00,
                    'discount_amount' => 5.00,
                    'qty' => 1,
                    'tax_amount' => 2.00,
                    'discount_tax_compensation_amount' => 0.00,
                    'order_item' => new \Magento\Framework\DataObject(
                        [
                            'product_options' => [
                                'options' => [['label' => 'Test Custom Option', 'value' => 'test value']],
                            ],
                        ]
                    ),
                ]
            )
        );
        $this->_model->expects($this->any())->method('getLinksTitle')->will($this->returnValue('Download Links'));
        $this->_model->expects(
            $this->any()
        )->method(
            'getLinks'
        )->will(
            $this->returnValue(
                new \Magento\Framework\DataObject(
                    ['purchased_items' => [
                        new \Magento\Framework\DataObject(['link_title' => 'Magento User Guide']), ],
                    ]
                )
            )
        );
        $this->_pdf->expects(
            $this->once()
        )->method(
            'drawLineBlocks'
        )->with(
            $this->anything(),
            $expectedPdfData,
            $expectedPageSettings
        )->will(
            $this->returnValue($expectedPdfPage)
        );

        $this->assertNotSame($expectedPdfPage, $this->_model->getPage());
        $this->assertNull($this->_model->draw());
        $this->assertSame($expectedPdfPage, $this->_model->getPage());
    }
}

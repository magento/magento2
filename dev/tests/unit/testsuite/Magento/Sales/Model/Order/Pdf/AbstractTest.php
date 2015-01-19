<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Pdf;

class AbstractTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test protected method to reduce testing complexity, which would be too high in case of testing a public method
     * without completing a huge refactoring of the class.
     */
    public function testInsertTotals()
    {
        // Setup parameters, that will be passed to the tested model method
        $page = $this->getMock('Zend_Pdf_Page', [], [], '', false);

        $order = new \StdClass();
        $source = $this->getMock('Magento\Sales\Model\Order\Invoice', [], [], '', false);
        $source->expects($this->any())->method('getOrder')->will($this->returnValue($order));

        // Setup most constructor dependencies
        $paymentData = $this->getMock('Magento\Payment\Helper\Data', [], [], '', false);
        $string = $this->getMock('Magento\Framework\Stdlib\String', [], [], '', false);
        $scopeConfig = $this->getMock('Magento\Framework\App\Config\ScopeConfigInterface');
        $translate = $this->getMock('Magento\Framework\Translate\Inline\StateInterface', [], [], '', false);
        $filesystem = $this->getMock('Magento\Framework\Filesystem', [], [], '', false);
        $pdfItemsFactory = $this->getMock('Magento\Sales\Model\Order\Pdf\ItemsFactory', [], [], '', false);
        $localeMock = $this->getMock(
            'Magento\Framework\Stdlib\DateTime\TimezoneInterface',
            [],
            [],
            '',
            false,
            false
        );

        // Setup config file totals
        $configTotals = ['item1' => [''], 'item2' => ['model' => 'custom_class']];
        $pdfConfig = $this->getMock('Magento\Sales\Model\Order\Pdf\Config', [], [], '', false);
        $pdfConfig->expects($this->once())->method('getTotals')->will($this->returnValue($configTotals));

        // Setup total factory
        $total1 = $this->getMock(
            'Magento\Sales\Model\Order\Pdf\Total\DefaultTotal',
            ['setSource', 'setOrder', 'canDisplay', 'getTotalsForDisplay'],
            [],
            '',
            false
        );
        $total1->expects($this->once())->method('setOrder')->with($order)->will($this->returnSelf());
        $total1->expects($this->once())->method('setSource')->with($source)->will($this->returnSelf());
        $total1->expects($this->once())->method('canDisplay')->will($this->returnValue(true));
        $total1->expects($this->once())
            ->method('getTotalsForDisplay')
            ->will($this->returnValue([['label' => 'label1', 'font_size' => 1, 'amount' => '$1']]));

        $total2 = $this->getMock(
            'Magento\Sales\Model\Order\Pdf\Total\DefaultTotal',
            ['setSource', 'setOrder', 'canDisplay', 'getTotalsForDisplay'],
            [],
            '',
            false
        );
        $total2->expects($this->once())->method('setOrder')->with($order)->will($this->returnSelf());
        $total2->expects($this->once())->method('setSource')->with($source)->will($this->returnSelf());
        $total2->expects($this->once())->method('canDisplay')->will($this->returnValue(true));
        $total2->expects($this->once())
            ->method('getTotalsForDisplay')
            ->will($this->returnValue([['label' => 'label2', 'font_size' => 2, 'amount' => '$2']]));

        $valueMap = [[null, [], $total1], ['custom_class', [], $total2]];
        $pdfTotalFactory = $this->getMock('Magento\Sales\Model\Order\Pdf\Total\Factory', [], [], '', false);
        $pdfTotalFactory->expects($this->exactly(2))->method('create')->will($this->returnValueMap($valueMap));

        // Test model
        /** @var \Magento\Sales\Model\Order\Pdf\AbstractPdf $model */
        $model = $this->getMockForAbstractClass(
            'Magento\Sales\Model\Order\Pdf\AbstractPdf',
            [
                $paymentData,
                $string,
                $scopeConfig,
                $filesystem,
                $pdfConfig,
                $pdfTotalFactory,
                $pdfItemsFactory,
                $localeMock,
                $translate
            ],
            '',
            true,
            false,
            true,
            ['drawLineBlocks']
        );
        $model->expects($this->once())->method('drawLineBlocks')->will($this->returnValue($page));

        $reflectionMethod = new \ReflectionMethod('Magento\Sales\Model\Order\Pdf\AbstractPdf', 'insertTotals');
        $reflectionMethod->setAccessible(true);
        $actual = $reflectionMethod->invoke($model, $page, $source);

        $this->assertSame($page, $actual);
    }
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Model\Order\Pdf;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AbstractTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test protected method to reduce testing complexity, which would be too high in case of testing a public method
     * without completing a huge refactoring of the class.
     */
    public function testInsertTotals()
    {
        // Setup parameters, that will be passed to the tested model method
        $page = $this->createMock(\Zend_Pdf_Page::class);

        $order = new \stdClass();
        $source = $this->createMock(\Magento\Sales\Model\Order\Invoice::class);
        $source->expects($this->any())->method('getOrder')->willReturn($order);

        // Setup most constructor dependencies
        $paymentData = $this->createMock(\Magento\Payment\Helper\Data::class);
        $addressRenderer = $this->createMock(\Magento\Sales\Model\Order\Address\Renderer::class);
        $string = $this->createMock(\Magento\Framework\Stdlib\StringUtils::class);
        $scopeConfig = $this->createMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        $translate = $this->createMock(\Magento\Framework\Translate\Inline\StateInterface::class);
        $filesystem = $this->createMock(\Magento\Framework\Filesystem::class);
        $pdfItemsFactory = $this->createMock(\Magento\Sales\Model\Order\Pdf\ItemsFactory::class);
        $localeMock = $this->createMock(\Magento\Framework\Stdlib\DateTime\TimezoneInterface::class);

        // Setup config file totals
        $configTotals = ['item1' => [''], 'item2' => ['model' => 'custom_class']];
        $pdfConfig = $this->createMock(\Magento\Sales\Model\Order\Pdf\Config::class);
        $pdfConfig->expects($this->once())->method('getTotals')->willReturn($configTotals);

        // Setup total factory
        $total1 = $this->createPartialMock(
            \Magento\Sales\Model\Order\Pdf\Total\DefaultTotal::class,
            ['setSource', 'setOrder', 'canDisplay', 'getTotalsForDisplay']
        );
        $total1->expects($this->once())->method('setOrder')->with($order)->willReturnSelf();
        $total1->expects($this->once())->method('setSource')->with($source)->willReturnSelf();
        $total1->expects($this->once())->method('canDisplay')->willReturn(true);
        $total1->expects($this->once())
            ->method('getTotalsForDisplay')
            ->willReturn([['label' => 'label1', 'font_size' => 1, 'amount' => '$1']]);

        $total2 = $this->createPartialMock(
            \Magento\Sales\Model\Order\Pdf\Total\DefaultTotal::class,
            ['setSource', 'setOrder', 'canDisplay', 'getTotalsForDisplay']
        );
        $total2->expects($this->once())->method('setOrder')->with($order)->willReturnSelf();
        $total2->expects($this->once())->method('setSource')->with($source)->willReturnSelf();
        $total2->expects($this->once())->method('canDisplay')->willReturn(true);
        $total2->expects($this->once())
            ->method('getTotalsForDisplay')
            ->willReturn([['label' => 'label2', 'font_size' => 2, 'amount' => '$2']]);

        $valueMap = [[null, [], $total1], ['custom_class', [], $total2]];
        $pdfTotalFactory = $this->createMock(\Magento\Sales\Model\Order\Pdf\Total\Factory::class);
        $pdfTotalFactory->expects($this->exactly(2))->method('create')->willReturnMap($valueMap);

        // Test model
        /** @var \Magento\Sales\Model\Order\Pdf\AbstractPdf $model */
        $model = $this->getMockForAbstractClass(
            \Magento\Sales\Model\Order\Pdf\AbstractPdf::class,
            [
                $paymentData,
                $string,
                $scopeConfig,
                $filesystem,
                $pdfConfig,
                $pdfTotalFactory,
                $pdfItemsFactory,
                $localeMock,
                $translate,
                $addressRenderer
            ],
            '',
            true,
            false,
            true,
            ['drawLineBlocks']
        );
        $model->expects($this->once())->method('drawLineBlocks')->willReturn($page);

        $reflectionMethod = new \ReflectionMethod(\Magento\Sales\Model\Order\Pdf\AbstractPdf::class, 'insertTotals');
        $reflectionMethod->setAccessible(true);
        $actual = $reflectionMethod->invoke($model, $page, $source);

        $this->assertSame($page, $actual);
    }
}

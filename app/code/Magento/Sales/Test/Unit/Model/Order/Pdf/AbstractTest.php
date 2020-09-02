<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\Order\Pdf;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Payment\Helper\Data;
use Magento\Sales\Model\Order\Address\Renderer;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Pdf\AbstractPdf;
use Magento\Sales\Model\Order\Pdf\Config;
use Magento\Sales\Model\Order\Pdf\ItemsFactory;
use Magento\Sales\Model\Order\Pdf\Total\DefaultTotal;
use Magento\Sales\Model\Order\Pdf\Total\Factory;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AbstractTest extends TestCase
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
        $source = $this->createMock(Invoice::class);
        $source->expects($this->any())->method('getOrder')->willReturn($order);

        // Setup most constructor dependencies
        $paymentData = $this->createMock(Data::class);
        $addressRenderer = $this->createMock(Renderer::class);
        $string = $this->createMock(StringUtils::class);
        $scopeConfig = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $translate = $this->getMockForAbstractClass(StateInterface::class);
        $filesystem = $this->createMock(Filesystem::class);
        $pdfItemsFactory = $this->createMock(ItemsFactory::class);
        $localeMock = $this->getMockForAbstractClass(TimezoneInterface::class);

        // Setup config file totals
        $configTotals = ['item1' => [''], 'item2' => ['model' => 'custom_class']];
        $pdfConfig = $this->createMock(Config::class);
        $pdfConfig->expects($this->once())->method('getTotals')->willReturn($configTotals);

        // Setup total factory
        $total1 = $this->getMockBuilder(DefaultTotal::class)
            ->addMethods(['setSource', 'setOrder'])
            ->onlyMethods(['canDisplay', 'getTotalsForDisplay'])
            ->disableOriginalConstructor()
            ->getMock();
        $total1->expects($this->once())->method('setOrder')->with($order)->willReturnSelf();
        $total1->expects($this->once())->method('setSource')->with($source)->willReturnSelf();
        $total1->expects($this->once())->method('canDisplay')->willReturn(true);
        $total1->expects($this->once())
            ->method('getTotalsForDisplay')
            ->willReturn([['label' => 'label1', 'font_size' => 1, 'amount' => '$1']]);

        $total2 = $this->getMockBuilder(DefaultTotal::class)
            ->addMethods(['setSource', 'setOrder'])
            ->onlyMethods(['canDisplay', 'getTotalsForDisplay'])
            ->disableOriginalConstructor()
            ->getMock();
        $total2->expects($this->once())->method('setOrder')->with($order)->willReturnSelf();
        $total2->expects($this->once())->method('setSource')->with($source)->willReturnSelf();
        $total2->expects($this->once())->method('canDisplay')->willReturn(true);
        $total2->expects($this->once())
            ->method('getTotalsForDisplay')
            ->willReturn([['label' => 'label2', 'font_size' => 2, 'amount' => '$2']]);

        $valueMap = [[null, [], $total1], ['custom_class', [], $total2]];
        $pdfTotalFactory = $this->createMock(Factory::class);
        $pdfTotalFactory->expects($this->exactly(2))->method('create')->willReturnMap($valueMap);

        // Test model
        /** @var AbstractPdf $model */
        $model = $this->getMockForAbstractClass(
            AbstractPdf::class,
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

        $reflectionMethod = new \ReflectionMethod(AbstractPdf::class, 'insertTotals');
        $reflectionMethod->setAccessible(true);
        $actual = $reflectionMethod->invoke($model, $page, $source);

        $this->assertSame($page, $actual);
    }
}

<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\Order\Pdf;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\File\Pdf\Image;
use Magento\Framework\Filesystem;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\MediaStorage\Helper\File\Storage\Database;
use Magento\Payment\Helper\Data;
use Magento\Sales\Model\RtlTextHandler;
use Magento\Sales\Model\Order\Address\Renderer;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Pdf\AbstractPdf;
use Magento\Sales\Model\Order\Pdf\Config;
use Magento\Sales\Model\Order\Pdf\ItemsFactory;
use Magento\Sales\Model\Order\Pdf\Total\DefaultTotal;
use Magento\Sales\Model\Order\Pdf\Total\Factory;
use Magento\Tax\Helper\Data as TaxHelper;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AbstractTest extends TestCase
{
    use MockCreationTrait;

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
        $scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $translate = $this->createMock(StateInterface::class);
        $filesystem = $this->createMock(Filesystem::class);
        $pdfItemsFactory = $this->createMock(ItemsFactory::class);
        $localeMock = $this->createMock(TimezoneInterface::class);
        $taxHelper = $this->createMock(TaxHelper::class);
        $fileStorageDatabase = $this->createMock(Database::class);
        $rtlTextHandler = $this->createMock(RtlTextHandler::class);
        $image = $this->createMock(Image::class);

        // Setup config file totals
        $configTotals = ['item1' => [''], 'item2' => ['model' => 'custom_class']];
        $pdfConfig = $this->createMock(Config::class);
        $pdfConfig->expects($this->once())->method('getTotals')->willReturn($configTotals);

        // Setup total factory
        $total1 = $this->createPartialMockWithReflection(
            DefaultTotal::class,
            ['setSource', 'setOrder', 'canDisplay', 'getTotalsForDisplay']
        );
        $total1->expects($this->once())->method('setOrder')->with($order)->willReturnSelf();
        $total1->expects($this->once())->method('setSource')->with($source)->willReturnSelf();
        $total1->expects($this->once())->method('canDisplay')->willReturn(true);
        $total1->expects($this->once())
            ->method('getTotalsForDisplay')
            ->willReturn([['label' => 'label1', 'font_size' => 1, 'amount' => '$1']]);

        $total2 = $this->createPartialMockWithReflection(
            DefaultTotal::class,
            ['setSource', 'setOrder', 'canDisplay', 'getTotalsForDisplay']
        );
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
        $model = $this->getMockBuilder(AbstractPdf::class)
            ->setConstructorArgs([
                $paymentData,
                $string,
                $scopeConfig,
                $filesystem,
                $pdfConfig,
                $pdfTotalFactory,
                $pdfItemsFactory,
                $localeMock,
                $translate,
                $addressRenderer,
                [],
                $fileStorageDatabase,
                $rtlTextHandler,
                $image,
                $taxHelper
            ])
            ->onlyMethods(['drawLineBlocks', 'getPdf'])
            ->getMock();
        $model->expects($this->once())->method('drawLineBlocks')->willReturn($page);

        $reflectionMethod = new \ReflectionMethod(AbstractPdf::class, 'insertTotals');
        $actual = $reflectionMethod->invoke($model, $page, $source);

        $this->assertSame($page, $actual);
    }

    /**
     * Test for the multiline text will be correctly wrapped between multiple pages
     *
     * @return void
     * @throws \ReflectionException
     */
    public function testDrawLineBlocks()
    {
        // Setup constructor dependencies
        $paymentData = $this->createMock(Data::class);
        $string = $this->createMock(StringUtils::class);
        $scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $filesystem = $this->createMock(Filesystem::class);
        $pdfConfig = $this->createMock(Config::class);
        $pdfTotalFactory = $this->createMock(Factory::class);
        $pdfItemsFactory = $this->createMock(ItemsFactory::class);
        $localeMock = $this->createMock(TimezoneInterface::class);
        $translate = $this->createMock(StateInterface::class);
        $addressRenderer = $this->createMock(Renderer::class);
        $taxHelper = $this->createMock(TaxHelper::class);
        $fileStorageDatabase = $this->createMock(Database::class);
        $rtlTextHandler = $this->createMock(RtlTextHandler::class);
        $image = $this->createMock(Image::class);

        $abstractPdfMock = $this->getMockBuilder(AbstractPdf::class)
            ->setConstructorArgs([
                $paymentData,
                $string,
                $scopeConfig,
                $filesystem,
                $pdfConfig,
                $pdfTotalFactory,
                $pdfItemsFactory,
                $localeMock,
                $translate,
                $addressRenderer,
                [],
                $fileStorageDatabase,
                $rtlTextHandler,
                $image,
                $taxHelper
            ])
            ->onlyMethods(['_setFontRegular', '_getPdf', 'getPdf'])
            ->getMock();

        $page = $this->createMock(\Zend_Pdf_Page::class);
        $zendFont = $this->createMock(\Zend_Pdf_Font::class);
        $zendPdf = $this->createMock(\Zend_Pdf::class);

        // Make sure that the newPage will be called 3 times to correctly break 200 lines into pages
        $zendPdf->expects($this->exactly(3))->method('newPage')->willReturn($page);

        $abstractPdfMock->expects($this->once())->method('_setFontRegular')->willReturn($zendFont);
        $abstractPdfMock->expects($this->any())->method('_getPdf')->willReturn($zendPdf);

        $reflectionMethod = new \ReflectionMethod(AbstractPdf::class, 'drawLineBlocks');

        $drawBlockLineData = $this->generateMultilineDrawBlock(200);
        $pageSettings = [
            'table_header' => true
        ];

        $reflectionMethod->invoke($abstractPdfMock, $page, $drawBlockLineData, $pageSettings);
    }

    /**
     * Validate page propagation between columns when a page break happens in the first column.
     *
     * @return void
     * @throws \ReflectionException
     */
    public function testDrawLineBlocksPropagatesNewPageToSiblingColumns(): void
    {
        $paymentData = $this->createMock(Data::class);
        $string = $this->createMock(StringUtils::class);
        $scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $filesystem = $this->createMock(Filesystem::class);
        $pdfConfig = $this->createMock(Config::class);
        $pdfTotalFactory = $this->createMock(Factory::class);
        $pdfItemsFactory = $this->createMock(ItemsFactory::class);
        $localeMock = $this->createMock(TimezoneInterface::class);
        $translate = $this->createMock(StateInterface::class);
        $addressRenderer = $this->createMock(Renderer::class);
        $taxHelper = $this->createMock(TaxHelper::class);
        $fileStorageDatabase = $this->createMock(Database::class);
        $rtlTextHandler = $this->createMock(RtlTextHandler::class);
        $image = $this->createMock(Image::class);

        $abstractPdfMock = $this->getMockBuilder(AbstractPdf::class)
            ->setConstructorArgs([
                $paymentData,
                $string,
                $scopeConfig,
                $filesystem,
                $pdfConfig,
                $pdfTotalFactory,
                $pdfItemsFactory,
                $localeMock,
                $translate,
                $addressRenderer,
                [],
                $fileStorageDatabase,
                $rtlTextHandler,
                $image,
                $taxHelper
            ])
            ->onlyMethods(['_setFontRegular', '_getPdf', 'getPdf'])
            ->getMock();

        $pageOne = $this->createMock(\Zend_Pdf_Page::class);
        $pageTwo = $this->createMock(\Zend_Pdf_Page::class);
        $zendFont = $this->createMock(\Zend_Pdf_Font::class);
        $zendPdf = $this->createMock(\Zend_Pdf::class);

        $abstractPdfMock->y = 25;

        $zendPdf->expects($this->once())
            ->method('newPage')
            ->willReturn($pageTwo);

        $abstractPdfMock->expects($this->atLeastOnce())
            ->method('_setFontRegular')
            ->willReturn($zendFont);
        $abstractPdfMock->expects($this->any())
            ->method('_getPdf')
            ->willReturn($zendPdf);

        $pageOne->expects($this->once())
            ->method('drawText')
            ->with('name-line-1', $this->anything(), $this->anything(), 'UTF-8');

        $drawnOnPageTwo = [];
        $pageTwo->expects($this->exactly(2))
            ->method('drawText')
            ->willReturnCallback(function ($text) use (&$drawnOnPageTwo) {
                $drawnOnPageTwo[] = $text;
            });

        $drawBlockLineData = [[
            'lines' => [[
                [
                    'text' => ['name-line-1', 'name-line-2'],
                    'feed' => 35
                ],
                [
                    'text' => ['sku-line'],
                    'feed' => 255
                ]
            ]],
            'height' => 20,
            'shift' => 5
        ]];

        $reflectionMethod = new \ReflectionMethod(AbstractPdf::class, 'drawLineBlocks');
        $resultPage = $reflectionMethod->invoke(
            $abstractPdfMock,
            $pageOne,
            $drawBlockLineData,
            ['table_header' => true]
        );

        $this->assertSame($pageTwo, $resultPage);
        $this->assertSame(['name-line-2', 'sku-line'], $drawnOnPageTwo);
    }

    /**
     * Generate the array for multiline block
     *
     * @param int $numberOfLines
     * @return array[]
     */
    private function generateMultilineDrawBlock(int $numberOfLines): array
    {
        $lines = [];
        for ($x = 0; $x < $numberOfLines; $x++) {
            $lines [] = $x;
        }

        $block = [
            [
                'lines' =>
                    [
                        [
                            [
                                'text' => $lines,
                                'feed' => 40
                            ]
                        ]
                    ],
                'shift' => 5
            ]
        ];

        return $block;
    }
}

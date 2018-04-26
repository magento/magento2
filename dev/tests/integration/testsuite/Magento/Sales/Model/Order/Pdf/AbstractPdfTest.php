<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Pdf;

use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Tests Sales Order PDF abstract model.
 *
 * @see \Magento\Sales\Model\Order\Pdf\AbstarctPdf
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AbstractPdfTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Tests Draw lines method.
     * Test case when text block cover more than one page.
     */
    public function testDrawLineBlocks()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        // Setup most constructor dependencies
        $paymentData = $objectManager->create(\Magento\Payment\Helper\Data::class);
        $string = $objectManager->create(\Magento\Framework\Stdlib\StringUtils::class);
        $scopeConfig = $objectManager->create(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        $filesystem = $objectManager->create(\Magento\Framework\Filesystem::class);
        $config = $objectManager->create(\Magento\Sales\Model\Order\Pdf\Config::class);
        $pdfTotalFactory = $objectManager->create(\Magento\Sales\Model\Order\Pdf\Total\Factory::class);
        $pdfItemsFactory = $objectManager->create(\Magento\Sales\Model\Order\Pdf\ItemsFactory::class);
        $locale = $objectManager->create(\Magento\Framework\Stdlib\DateTime\TimezoneInterface::class);
        $translate = $objectManager->create(\Magento\Framework\Translate\Inline\StateInterface::class);
        $addressRenderer = $objectManager->create(\Magento\Sales\Model\Order\Address\Renderer::class);

        // Test model
        /** @var \Magento\Sales\Model\Order\Pdf\AbstractPdf|MockObject $model */
        $model = $this->getMockForAbstractClass(
            \Magento\Sales\Model\Order\Pdf\AbstractPdf::class,
            [
                $paymentData,
                $string,
                $scopeConfig,
                $filesystem,
                $config,
                $pdfTotalFactory,
                $pdfItemsFactory,
                $locale,
                $translate,
                $addressRenderer,
            ],
            '',
            true,
            true,
            true,
            ['getPdf', '_getPdf']
        );
        $pdf = new \Zend_Pdf();
        $model->expects($this->any())->method('getPdf')->will($this->returnValue($pdf));
        $model->expects($this->any())->method('_getPdf')->will($this->returnValue($pdf));

        /** Generate multiline block, that cover more than one page */
        $lines = [];
        for ($lineNumber = 1; $lineNumber <= 100; $lineNumber++) {
            $lines[] = [[
                'feed' => 0,
                'font_size' => 10,
                'text' => 'Text line ' . $lineNumber,
            ]];
        }
        $draw = [[
            'height' => 12,
            'lines' => $lines,
        ]];

        $page = $model->newPage(['page_size' => \Zend_Pdf_Page::SIZE_A4]);

        $model->drawLineBlocks($page, $draw);
        $this->assertEquals(
            3,
            count($pdf->pages)
        );
    }
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TaxImportExport\Block\Adminhtml\Rate;

class ImportExportTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\TaxImportExport\Block\Adminhtml\Rate\ImportExport
     */
    protected $_block = null;

    protected function setUp(): void
    {
        \Magento\TestFramework\Helper\Bootstrap::getInstance()
            ->loadArea(\Magento\Backend\App\Area\FrontNameResolver::AREA_CODE);
        $this->_block = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Framework\View\LayoutInterface::class
        )->createBlock(\Magento\TaxImportExport\Block\Adminhtml\Rate\ImportExport::class);
    }

    protected function tearDown(): void
    {
        $this->_block = null;
    }

    public function testCreateBlock()
    {
        $this->assertInstanceOf(\Magento\TaxImportExport\Block\Adminhtml\Rate\ImportExport::class, $this->_block);
    }

    public function testFormExists()
    {
        $html = $this->_block->toHtml();

        $this->assertStringContainsString('<form id="import-form"', $html);

        $this->assertStringContainsString('<form id="export_form"', $html);
    }
}

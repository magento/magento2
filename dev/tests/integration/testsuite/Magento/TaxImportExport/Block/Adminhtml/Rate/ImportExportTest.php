<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TaxImportExport\Block\Adminhtml\Rate;

class ImportExportTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TaxImportExport\Block\Adminhtml\Rate\ImportExport
     */
    protected $_block = null;

    protected function setUp()
    {
        \Magento\TestFramework\Helper\Bootstrap::getInstance()
            ->loadArea(\Magento\Backend\App\Area\FrontNameResolver::AREA_CODE);
        $this->_block = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Framework\View\LayoutInterface::class
        )->createBlock(\Magento\TaxImportExport\Block\Adminhtml\Rate\ImportExport::class);
    }

    protected function tearDown()
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

        $this->assertContains('<form id="import-form"', $html);

        $this->assertContains('<form id="export_form"', $html);
    }
}

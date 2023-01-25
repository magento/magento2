<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TaxImportExport\Block\Adminhtml\Rate;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Tests for Tax Rate Import/Export form.
 *
 * @magentoAppArea adminhtml
 */
class ImportExportTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var ImportExport
     */
    protected $block = null;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->block = $this->objectManager->get(LayoutInterface::class)->createBlock(ImportExport::class);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $this->block = null;
    }

    /**
     * @return void
     */
    public function testCreateBlock(): void
    {
        $this->assertInstanceOf(ImportExport::class, $this->block);
    }

    /**
     * @return void
     */
    public function testFormExists(): void
    {
        $html = $this->block->toHtml();
        $this->assertStringContainsString('<form id="import-form"', $html);
        $this->assertStringContainsString('<form id="export_form"', $html);
    }

    /**
     * @return void
     */
    public function testExportFormButtonOnClick(): void
    {
        $html = $this->block->toHtml();
        $this->assertStringContainsString('<form id="export_form"', $html);
        $this->assertStringContainsString('export_form.submit();', $html);
    }
}

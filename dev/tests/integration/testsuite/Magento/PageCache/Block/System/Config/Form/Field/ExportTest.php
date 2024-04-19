<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\PageCache\Block\System\Config\Form\Field;

/**
 * @magentoAppArea adminhtml
 */
class ExportTest extends \Magento\TestFramework\TestCase\AbstractBackendController
{
    /**
     * Check Varnish export buttons
     * @covers \Magento\PageCache\Block\System\Config\Form\Field\Export::_getElementHtml
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     */
    public function testExportButtons()
    {
        $this->dispatch('backend/admin/system_config/edit/section/system/');
        $body = $this->getResponse()->getBody();
        $this->assertStringContainsString('[id^=system_full_page_cache_varnish_export_button_version]', $body);
    }
}

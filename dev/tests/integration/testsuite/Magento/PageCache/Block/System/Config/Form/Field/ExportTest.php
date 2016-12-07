<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
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
     * @covers \Magento\PageCache\Block\System\Config\Form\Field\Export\Varnish5::getVarnishVersion
     * @covers \Magento\PageCache\Block\System\Config\Form\Field\Export\Varnish4::getVarnishVersion
     */
    public function testExportButtons()
    {
        $this->dispatch('backend/admin/system_config/edit/section/system/');
        $body = $this->getResponse()->getBody();
        $this->assertContains('system_full_page_cache_varnish_export_button_version3', $body);
        $this->assertContains('system_full_page_cache_varnish_export_button_version4', $body);
        $this->assertContains('[id^=system_full_page_cache_varnish_export_button_version]', $body);
    }
}

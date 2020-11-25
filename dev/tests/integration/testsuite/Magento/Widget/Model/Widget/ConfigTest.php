<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Widget\Model\Widget;

/**
 * @magentoAppArea adminhtml
 */
class ConfigTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Widget\Model\Widget\Config
     */
    protected $_model;

    protected function setUp(): void
    {
        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Widget\Model\Widget\Config::class
        );
    }

    /**
     * App isolation is enabled, because we change current area and design
     *
     * @magentoAppIsolation enabled
     */
    public function testGetPluginSettings()
    {
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Framework\View\DesignInterface::class
        )->setDesignTheme(
            'Magento/backend'
        );

        $config = new \Magento\Framework\DataObject();
        $settings = $this->_model->getPluginSettings($config);

        $this->assertArrayHasKey('plugins', $settings);
        $plugins = array_shift($settings['plugins']);
        $this->assertArrayHasKey('options', $plugins);
        $this->assertArrayHasKey('window_url', $plugins['options']);
        $this->assertArrayHasKey('placeholders', $plugins['options']);

        $jsFilename = $plugins['src'];
        $this->assertStringMatchesFormat(
            'http://localhost/static/%s/adminhtml/Magento/backend/en_US/%s/editor_plugin.js',
            $jsFilename
        );

        $this->assertIsArray($plugins['options']['placeholders']);

        $this->assertStringStartsWith(
            'http://localhost/index.php/backend/admin/widget/index/key',
            $plugins['options']['window_url']
        );
    }

    public function testGetWidgetWindowUrl()
    {
        $config = new \Magento\Framework\DataObject(['widget_filters' => ['is_email_compatible' => 1]]);

        $url = $this->_model->getWidgetWindowUrl($config);

        $this->assertStringStartsWith('http://localhost/index.php/backend/admin/widget/index/skip_widgets', $url);
    }
}

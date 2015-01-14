<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Widget\Model\Widget;

/**
 * @magentoAppArea adminhtml
 */
class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Widget\Model\Widget\Config
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Widget\Model\Widget\Config'
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
            'Magento\Framework\View\DesignInterface'
        )->setDesignTheme(
            'Magento/backend'
        );

        $config = new \Magento\Framework\Object();
        $settings = $this->_model->getPluginSettings($config);

        $this->assertArrayHasKey('widget_plugin_src', $settings);
        $this->assertArrayHasKey('widget_placeholders', $settings);
        $this->assertArrayHasKey('widget_window_url', $settings);

        $jsFilename = $settings['widget_plugin_src'];
        $this->assertStringStartsWith('http://localhost/pub/static/adminhtml/Magento/backend/en_US/', $jsFilename);
        $this->assertStringEndsWith('editor_plugin.js', $jsFilename);

        $this->assertInternalType('array', $settings['widget_placeholders']);

        $this->assertStringStartsWith(
            'http://localhost/index.php/backend/admin/widget/index/key',
            $settings['widget_window_url']
        );
    }

    public function testGetWidgetWindowUrl()
    {
        $config = new \Magento\Framework\Object(['widget_filters' => ['is_email_compatible' => 1]]);

        $url = $this->_model->getWidgetWindowUrl($config);

        $this->assertStringStartsWith('http://localhost/index.php/backend/admin/widget/index/skip_widgets', $url);
    }
}

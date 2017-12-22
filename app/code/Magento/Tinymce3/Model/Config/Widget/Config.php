<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tinymce3\Model\Config\Widget;

class Config implements \Magento\Config\Model\Wysiwyg\ConfigInterface
{
    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    private $assetRepo;

    /**
     * @var \Magento\Widget\Model\Widget\Config
     */
    private $widgetConfig;

    /**
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     * @param \Magento\Widget\Model\Widget\Config $widgetConfig
     */
    public function __construct(
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Widget\Model\Widget\Config $widgetConfig
    ) {
        $this->assetRepo = $assetRepo;
        $this->widgetConfig = $widgetConfig;
    }

    /**
     * @param \Magento\Framework\DataObject $config
     * @return array
     */
    public function getConfig($config)
    {
        /** @todo override config without widget dependency */
        $result = $this->widgetConfig->getConfig($config);
        $magento_widget_plugin_arr_index = array_search('magentowidget', array_column($result['plugins'], 'name'));

        if ($magento_widget_plugin_arr_index !== false) {
            $widget_plugin_options = $result['plugins'][$magento_widget_plugin_arr_index];
            $result = [
                'widget_plugin_src' => $this->getWysiwygJsPluginSrc(),
                'widget_window_url' => $widget_plugin_options['options']['window_url'],
                'widget_types' => $widget_plugin_options['options']['types'],
                'widget_error_image_url' => $widget_plugin_options['options']['error_image_url'],
                'widget_placeholders' => $result['widget_placeholders']
            ];
        }

        return $result;
    }

    /**
     * @return string
     */
    private function getWysiwygJsPluginSrc()
    {
        $editorPluginJs = 'Magento_Tinymce3::tiny_mce/plugins/magentowidget/editor_plugin.js';
        $result = $this->assetRepo->getUrl($editorPluginJs);
        return $result;
    }
}

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
        $widgetPluginArrIndex = array_search('magentowidget', array_column($result['plugins'], 'name'));

        if ($widgetPluginArrIndex !== false) {
            $widgetPluginOptions = $result['plugins'][$widgetPluginArrIndex]['options'];
            $result = [
                'widget_plugin_src' => $this->getWysiwygJsPluginSrc(),
                'widget_window_url' => $widgetPluginOptions['window_url'],
                'widget_types' => $widgetPluginOptions['types'],
                'widget_error_image_url' => $widgetPluginOptions['error_image_url'],
                'widget_placeholders' => $widgetPluginOptions['placeholders']
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

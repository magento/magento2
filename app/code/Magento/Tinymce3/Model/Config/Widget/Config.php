<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Tinymce3\Model\Config\Widget;

/**
 * Class Config adds widget plugin information required for tinymce3 editor
 * @deprecated use \Magento\Widget\Model\Widget\Config instead
 */
class Config implements \Magento\Framework\Data\Wysiwyg\ConfigProviderInterface
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
     * {@inheritdoc}
     */
    public function getConfig(\Magento\Framework\DataObject $config) : \Magento\Framework\DataObject
    {
        $settings = [
            'widget_plugin_src' => $this->getWysiwygJsPluginSrc(),
            'widget_window_url' => $this->widgetConfig->getWidgetWindowUrl($config),
            'widget_types' => $this->widgetConfig->getAvailableWidgets($config),
            'widget_error_image_url' => $this->widgetConfig->getErrorImageUrl(),
            'widget_placeholders' => $this->widgetConfig->getWidgetPlaceholderImageUrls()
        ];
        return $config->addData($settings);
    }

    /**
     * Return path to tinymce3 widget plugin
     *
     * @return string
     */
    private function getWysiwygJsPluginSrc() : string
    {
        $editorPluginJs = 'Magento_Tinymce3::tiny_mce/plugins/magentowidget/editor_plugin.js';
        $result = $this->assetRepo->getUrl($editorPluginJs);
        return $result;
    }
}

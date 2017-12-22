<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tinymce3\Model\Plugin\Widget;

use Magento\Tinymce3\Model\Config\Source\Wysiwyg\Editor;

class Config
{
    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    private $assetRepo;

    /**
     * @var \Magento\Ui\Block\Wysiwyg\ActiveEditor
     */
    private $activeEditor;

    /**
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     * @param \Magento\Ui\Block\Wysiwyg\ActiveEditor $activeEditor
     */
    public function __construct(
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Ui\Block\Wysiwyg\ActiveEditor $activeEditor
    ) {
        $this->assetRepo = $assetRepo;
        $this->activeEditor = $activeEditor;
    }

    /**
     * @param \Magento\Widget\Model\Widget\Config $subject
     * @param array $result
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetPluginSettings(
        \Magento\Widget\Model\Widget\Config $subject,
        $result
    ) {
        if ($this->activeEditor->getWysiwygAdapterPath() === Editor::WYSIWYG_EDITOR_CONFIG_VALUE) {
            $magento_widget_plugin_arr_index = array_search('magentowidget', array_column($result['plugins'], 'name'));

            if ($magento_widget_plugin_arr_index !== false) {
                $widget_plugin_options = $result['plugins'][$magento_widget_plugin_arr_index];

                $result = [
                    'widget_plugin_src' => $this->getWysiwygJsPluginSrc(),
                    'widget_placeholders' => $result['widget_placeholders'],
                    'widget_window_url' => $widget_plugin_options['options']['window_url'],
                    'widget_types' => $widget_plugin_options['options']['types'],
                    'widget_error_image_url' => $widget_plugin_options['options']['error_image_url'],
                ];
            }
        }

        return $result;
    }

    /**
     * @param \Magento\Widget\Model\Widget\Config $subject
     * @param string $result
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetWysiwygJsPluginSrc(
        \Magento\Widget\Model\Widget\Config $subject,
        $result
    ) {
        if ($this->activeEditor->getWysiwygAdapterPath() === Editor::WYSIWYG_EDITOR_CONFIG_VALUE) {
            $result = $this->getWysiwygJsPluginSrc();
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

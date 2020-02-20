<?php
/**le
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);
namespace Magento\Tinymce3\Model\Config\Variable;

/**
 * Class Config adds variable plugin information required for tinymce3 editor
 * @deprecated use \Magento\Variable\Model\Variable\ConfigProvider instead
 */
class Config implements \Magento\Framework\Data\Wysiwyg\ConfigProviderInterface
{
    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    private $assetRepo;

    /**
     * @var \Magento\Variable\Model\Variable\Config
     */
    private $defaultVariableConfig;

    /**
     * @param \Magento\Variable\Model\Variable\Config $defaultVariableConfig
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     */
    public function __construct(
        \Magento\Variable\Model\Variable\Config $defaultVariableConfig,
        \Magento\Framework\View\Asset\Repository $assetRepo
    ) {
        $this->assetRepo = $assetRepo;
        $this->defaultVariableConfig = $defaultVariableConfig;
    }

    /**
     * Update variable plugin url
     *
     * @param \Magento\Framework\DataObject $config
     * @return \Magento\Framework\DataObject
     */
    public function getConfig(\Magento\Framework\DataObject $config) : \Magento\Framework\DataObject
    {
        $settings = $this->defaultVariableConfig->getWysiwygPluginSettings($config);
        $pluginConfig = isset($settings['plugins']) ? $settings['plugins'] : [];
        $pluginData = [];
        if (!empty($pluginConfig)) {
            $pluginData = array_shift($pluginConfig);
            $editorPluginJs = 'Magento_Tinymce3::wysiwyg/tiny_mce/plugins/magentovariable/editor_plugin.js';
            $pluginData['src'] = $this->assetRepo->getUrl($editorPluginJs);
            $settings['variable_placeholders'] = $pluginData['options']['placeholders'];
        }
        $settings['plugins'] = [$pluginData];
        return $config->addData($settings);
    }
}

<?php
/**le
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tinymce3\Model\Config\Variable;

class Config implements \Magento\Config\Model\Wysiwyg\ConfigInterface
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
     * @return array
     */
    public function getConfig($config)
    {
        $settings = $this->defaultVariableConfig->getConfig($config);
        $pluginConfig = isset($settings['plugins']) ? $settings['plugins'] : [];
        $pluginData = [];
        if (!empty($pluginConfig)) {
            $pluginData = array_shift($pluginConfig);
            $editorPluginJs = 'Magento_Tinymce3::wysiwyg/tiny_mce/plugins/magentovariable/editor_plugin.js';
            $pluginData['src'] = $this->assetRepo->getUrl($editorPluginJs);
            $settings['variable_placeholders'] = $pluginData['placeholders'];
        }
        $settings['plugins'] = [$pluginData];
        return $settings;
    }
}

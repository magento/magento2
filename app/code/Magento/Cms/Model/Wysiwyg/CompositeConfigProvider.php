<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Cms\Model\Wysiwyg;

/**
 * Class ConfigProcessor
 * @package Magento\Cms\Model
 */
class CompositeConfigProvider
{
    /**
     * @var \Magento\Ui\Block\Wysiwyg\ActiveEditor
     */
    private $activeEditor;

    /**
     * List of postProcessors by adapter type
     *
     * @var array
     */
    private $variablePluginConfigProvider;

    /**
     * @var array
     */
    private $widgetPluginConfigProvider;

    /**
     * @var array
     */
    private $wysiwygConfigPostProcessor;

    /**
     * @var \Magento\Cms\Model\Wysiwyg\ConfigProviderFactory
     */
    private $configProviderFactory;

    /**
     * @var string
     */
    private $activeEditorPath;

    /**
     * ConfigProcessor constructor.
     *
     * @param \Magento\Ui\Block\Wysiwyg\ActiveEditor $activeEditor
     * @param \Magento\Cms\Model\Wysiwyg\ConfigProviderFactory $configProviderFactory
     * @param array $variablePluginConfigProvider
     * @param array $widgetPluginConfigProvider
     * @param array $wysiwygConfigPostProcessor
     */
    public function __construct(
        \Magento\Ui\Block\Wysiwyg\ActiveEditor $activeEditor,
        \Magento\Cms\Model\Wysiwyg\ConfigProviderFactory $configProviderFactory,
        array $variablePluginConfigProvider,
        array $widgetPluginConfigProvider,
        array $wysiwygConfigPostProcessor
    ) {
        $this->activeEditor = $activeEditor;
        $this->configProviderFactory = $configProviderFactory;
        $this->variablePluginConfigProvider = $variablePluginConfigProvider;
        $this->widgetPluginConfigProvider = $widgetPluginConfigProvider;
        $this->wysiwygConfigPostProcessor = $wysiwygConfigPostProcessor;
    }

    /**
     * @param \Magento\Framework\DataObject $config
     * @return array
     */
    public function processVariableConfig($config)
    {
        return $this->updateConfig($config, $this->variablePluginConfigProvider);
    }

    /**
     * @param \Magento\Framework\DataObject $config
     * @return array
     */
    public function processWidgetConfig($config)
    {
        return $this->updateConfig($config, $this->widgetPluginConfigProvider);
    }

    /**
     * @param \Magento\Framework\DataObject $config
     * @return \Magento\Framework\DataObject
     */
    public function processWysiwygConfig($config)
    {
        return $this->updateConfig($config, $this->wysiwygConfigPostProcessor);
    }

    /**
     * @return string
     */
    private function getActiveEditorPath()
    {
        if (!isset($this->activeEditorPath)) {
            $this->activeEditorPath = $this->activeEditor->getWysiwygAdapterPath();
        }
        return $this->activeEditorPath;
    }

    /**
     * @param \Magento\Framework\DataObject $config
     * @param array $configProviders
     * @return \Magento\Framework\DataObject|array
     */
    private function updateConfig($config, array $configProviders)
    {
        $adapterType = $this->getActiveEditorPath();
        //Extension point to update plugin settings by adapter type
        $providerClass = isset($configProviders[$adapterType])
            ? $configProviders[$adapterType]
            : $configProviders['default'];
        /** @var \Magento\Config\Model\Wysiwyg\ConfigInterface $provider */
        $provider = $this->configProviderFactory->create($providerClass);
        return $provider->getConfig($config);
    }
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Cms\Model\Wysiwyg;

/**
 * Class CompositeConfigProvider loads required config by adapter specified in system configuration
 * General > Content Management >WYSIWYG Options > WYSIWYG Editor
 */
class CompositeConfigProvider
{
    /**
     * @var \Magento\Ui\Block\Wysiwyg\ActiveEditor
     */
    private $activeEditor;

    /**
     * List of variable config processors by adapter type
     *
     * @var array
     */
    private $variablePluginConfigProvider;

    /**
     * List of widget config processors by adapter type
     *
     * @var array
     */
    private $widgetPluginConfigProvider;

    /**
     * List of wysiwyg config postprocessors by adapter type
     *
     * @var array
     */
    private $wysiwygConfigPostProcessor;

    /**
     * Factory to create required processor object
     *
     * @var \Magento\Cms\Model\Wysiwyg\ConfigProviderFactory
     */
    private $configProviderFactory;

    /**
     * Current active editor path
     *
     * @var string
     */
    private $activeEditorPath;

    /**
     * List of gallery config processors by adapter type
     *
     * @var array
     */
    private $galleryConfigProvider;

    /**
     * @param \Magento\Ui\Block\Wysiwyg\ActiveEditor $activeEditor
     * @param ConfigProviderFactory $configProviderFactory
     * @param array $variablePluginConfigProvider
     * @param array $widgetPluginConfigProvider
     * @param array $galleryConfigProvider
     * @param array $wysiwygConfigPostProcessor
     */
    public function __construct(
        \Magento\Ui\Block\Wysiwyg\ActiveEditor $activeEditor,
        \Magento\Cms\Model\Wysiwyg\ConfigProviderFactory $configProviderFactory,
        array $variablePluginConfigProvider,
        array $widgetPluginConfigProvider,
        array $galleryConfigProvider,
        array $wysiwygConfigPostProcessor
    ) {
        $this->activeEditor = $activeEditor;
        $this->configProviderFactory = $configProviderFactory;
        $this->variablePluginConfigProvider = $variablePluginConfigProvider;
        $this->widgetPluginConfigProvider = $widgetPluginConfigProvider;
        $this->galleryConfigProvider = $galleryConfigProvider;
        $this->wysiwygConfigPostProcessor = $wysiwygConfigPostProcessor;
    }

    /**
     * Add config for variable plugin
     *
     * @param \Magento\Framework\DataObject $config
     * @return \Magento\Framework\DataObject
     */
    public function processVariableConfig($config)
    {
        return $this->updateConfig($config, $this->variablePluginConfigProvider);
    }

    /**
     * Add config for widget plugin
     *
     * @param \Magento\Framework\DataObject $config
     * @return \Magento\Framework\DataObject
     */
    public function processWidgetConfig($config)
    {
        return $this->updateConfig($config, $this->widgetPluginConfigProvider);
    }

    /**
     * Add config for gallery
     *
     * @param \Magento\Framework\DataObject $config
     * @return \Magento\Framework\DataObject
     */
    public function processGalleryConfig($config)
    {
        return $this->updateConfig($config, $this->galleryConfigProvider);
    }

    /**
     * Update wysiwyg config with data required for adapter
     *
     * @param \Magento\Framework\DataObject $config
     * @return \Magento\Framework\DataObject
     */
    public function processWysiwygConfig($config)
    {
        return $this->updateConfig($config, $this->wysiwygConfigPostProcessor);
    }

    /**
     * Returns active editor path
     *
     * @param \Magento\Framework\DataObject $config
     * @return string
     */
    private function getActiveEditorPath($config)
    {
        if (!isset($this->activeEditorPath) || $this->activeEditorPath !== $config->getData('activeEditorPath')) {
            $this->activeEditorPath = $config->getData('activeEditorPath')
                ? $config->getData('activeEditorPath')
                : $this->activeEditor->getWysiwygAdapterPath();
            $config->setData('activeEditorPath', $this->activeEditorPath);
        }
        return $this->activeEditorPath;
    }

    /**
     * Update config using config provider by active editor path
     *
     * @param \Magento\Framework\DataObject $config
     * @param array $configProviders
     * @return \Magento\Framework\DataObject
     */
    private function updateConfig($config, array $configProviders)
    {
        $adapterType = $this->getActiveEditorPath($config);
        //Extension point to update plugin settings by adapter type
        $providerClass = isset($configProviders[$adapterType])
            ? $configProviders[$adapterType]
            : $configProviders['default'];
        /** @var \Magento\Framework\Data\Wysiwyg\ConfigProviderInterface $provider */
        $provider = $this->configProviderFactory->create($providerClass);
        return $provider->getConfig($config);
    }
}

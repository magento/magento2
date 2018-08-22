<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestModuleWysiwygConfig\Model;

class Config implements \Magento\Framework\Data\Wysiwyg\ConfigProviderInterface
{
    /**
     * Configuration override for WYSIWYG height
     * @var string
     */
    const CONFIG_HEIGHT = 'something_else';

    /**
     * Configuration override for WYSIWYG content css
     * @var string
     */
    const CONFIG_CONTENT_CSS = 'something_else.css';

    /** @var \Magento\Cms\Model\Wysiwyg\DefaultConfigProvider */
    private $cmsConfigProvider;

    /**
     * @param \Magento\Cms\Model\Wysiwyg\DefaultConfigProvider $cmsConfigProvider
     */
    public function __construct(\Magento\Cms\Model\Wysiwyg\DefaultConfigProvider $cmsConfigProvider)
    {
        $this->cmsConfigProvider = $cmsConfigProvider;
    }

    /**
     * @inheritdoc
     */
    public function getConfig(\Magento\Framework\DataObject $config): \Magento\Framework\DataObject
    {
        //get default config
        $config = $this->cmsConfigProvider->getConfig($config);

        $config = $this->removeSpecialCharacterFromToolbar($config);

        $config = $this->modifyHeightAndContentCss($config);
        return $config;
    }

    /**
     * Modify height and content_css in the config
     *
     * @param \Magento\Framework\DataObject $config
     * @return \Magento\Framework\DataObject
     */
    private function modifyHeightAndContentCss(\Magento\Framework\DataObject $config) : \Magento\Framework\DataObject
    {
        return $config->addData(
            [
                'height' => self::CONFIG_HEIGHT,
                'content_css' => self::CONFIG_CONTENT_CSS
            ]
        );
    }

    /**
     * Remove the special character from the toolbar configuration
     *
     * @param \Magento\Framework\DataObject $config
     * @return \Magento\Framework\DataObject
     */
    private function removeSpecialCharacterFromToolbar(
        \Magento\Framework\DataObject $config
    ) : \Magento\Framework\DataObject {
        $tinymce4 = $config->getData('tinymce4');
        if (isset($tinymce4['toolbar']) && isset($tinymce4['plugins'])) {
            $toolbar = $tinymce4['toolbar'];
            $plugins = $tinymce4['plugins'];
            $tinymce4['toolbar'] = str_replace('charmap', '', $toolbar);
            $tinymce4['plugins'] = str_replace('charmap', '', $plugins);
            $config->setData('tinymce4', $tinymce4);
        }
        return $config;
    }
}

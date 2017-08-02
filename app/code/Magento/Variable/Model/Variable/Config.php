<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Variable\Model\Variable;

/**
 * Variable Wysiwyg Plugin Config
 *
 * @api
 * @since 2.0.0
 */
class Config
{
    /**
     * @var \Magento\Framework\View\Asset\Repository
     * @since 2.0.0
     */
    protected $_assetRepo;

    /**
     * @var \Magento\Backend\Model\UrlInterface
     * @since 2.0.0
     */
    protected $_url;

    /**
     * Constructor
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     * @param \Magento\Backend\Model\UrlInterface $url
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Backend\Model\UrlInterface $url
    ) {
        $this->_assetRepo = $assetRepo;
        $this->_url = $url;
    }

    /**
     * Prepare variable wysiwyg config
     *
     * @param \Magento\Framework\DataObject $config
     * @return array
     * @since 2.0.0
     */
    public function getWysiwygPluginSettings($config)
    {
        $variableConfig = [];
        $onclickParts = [
            'search' => ['html_id'],
            'subject' => 'MagentovariablePlugin.loadChooser(\'' .
            $this->getVariablesWysiwygActionUrl() .
            '\', \'{{html_id}}\');',
        ];
        $variableWysiwyg = [
            [
                'name' => 'magentovariable',
                'src' => $this->getWysiwygJsPluginSrc(),
                'options' => [
                    'title' => __('Insert Variable...'),
                    'url' => $this->getVariablesWysiwygActionUrl(),
                    'onclick' => $onclickParts,
                    'class' => 'add-variable plugin',
                ],
            ],
        ];
        $configPlugins = $config->getData('plugins');
        $variableConfig['plugins'] = array_merge($configPlugins, $variableWysiwyg);
        return $variableConfig;
    }

    /**
     * Return url to wysiwyg plugin
     *
     * @return string
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function getWysiwygJsPluginSrc()
    {
        $editorPluginJs = 'mage/adminhtml/wysiwyg/tiny_mce/plugins/magentovariable/editor_plugin.js';
        return $this->_assetRepo->getUrl($editorPluginJs);
    }

    /**
     * Return url of action to get variables
     *
     * @return string
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function getVariablesWysiwygActionUrl()
    {
        return $this->_url->getUrl('adminhtml/system_variable/wysiwygPlugin');
    }
}

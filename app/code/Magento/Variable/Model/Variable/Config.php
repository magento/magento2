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
 * @since 100.0.2
 */
class Config
{
    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    protected $_assetRepo;

    /**
     * @var \Magento\Backend\Model\UrlInterface
     */
    protected $_url;

    /**
     * @var \Magento\Variable\Model\ResourceModel\Variable\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var \Magento\Variable\Model\Source\Variables
     */
    private $storesVariables;

    /**
     * Config constructor.
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     * @param \Magento\Backend\Model\UrlInterface $url
     * @param \Magento\Variable\Model\Variable $variable
     * @param \Magento\Variable\Model\ResourceModel\Variable\CollectionFactory $collectionFactory
     * @param \Magento\Variable\Model\Source\Variables $storesVariables
     * @param \Magento\Framework\Serialize\Serializer\Json $encoder
     */
    public function __construct(
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Backend\Model\UrlInterface $url,
        \Magento\Variable\Model\ResourceModel\Variable\CollectionFactory $collectionFactory,
        \Magento\Variable\Model\Source\Variables $storesVariables,
        \Magento\Framework\Serialize\Serializer\Json $encoder
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->storesVariables = $storesVariables;
        $this->encoder = $encoder;
        $this->_assetRepo = $assetRepo;
        $this->_url = $url;
    }

    /**
     * Prepare variable wysiwyg config
     *
     * @param \Magento\Framework\DataObject $config
     * @return array
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
                    'variable_placeholders' => $this->getVariablesWysiwygDataUrl(),
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
     */
    public function getVariablesWysiwygActionUrl()
    {
        return $this->_url->getUrl('mui/index/render',
            ['namespace' => 'variables_modal']
        );
    }


    /**
     * Prepare default variables
     *
     * @return array
     */
    private function getDefaultVariables()
    {
        $variables = [];
        foreach ($this->storesVariables->getData() as $variable) {
            $variables[$variable['value']] = [
                'code' => $variable['value'],
                'variable_name' => $variable['label'],
                'variable_type' => \Magento\Variable\Model\Source\Variables::DEFAULT_VARIABLE_TYPE
            ];
        }

        return $variables;
    }

    /**
     * Prepare custom variables
     *
     * @return array
     */
    private function getCustomVariables()
    {
        $customVariables = $this->collectionFactory->create();

        $variables = [];
        foreach ($customVariables->getData() as $variable) {
            $variables[$variable['code']] = [
                'code' => $variable['code'],
                'variable_name' => $variable['name'],
                'variable_type' => 'custom'
            ];
        }

        return $variables;
    }
    /**
     * @return string
     */
    private function getVariablesWysiwygDataUrl()
    {
        $variablesData = array_merge(
            $this->getCustomVariables(),
            $this->getDefaultVariables()
        );
        return $this->encoder->serialize($variablesData);
    }
}

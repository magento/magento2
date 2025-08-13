<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Variable\Model\Variable;

use Magento\Framework\App\ObjectManager;

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
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $encoder;

    /**
     * Config constructor.
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     * @param \Magento\Backend\Model\UrlInterface $url
     * @param \Magento\Variable\Model\ResourceModel\Variable\CollectionFactory|null $collectionFactory
     * @param \Magento\Variable\Model\Source\Variables|null $storesVariables
     * @param \Magento\Framework\Serialize\Serializer\Json|null $encoder
     */
    public function __construct(
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Backend\Model\UrlInterface $url,
        ?\Magento\Variable\Model\ResourceModel\Variable\CollectionFactory $collectionFactory = null,
        ?\Magento\Variable\Model\Source\Variables $storesVariables = null,
        ?\Magento\Framework\Serialize\Serializer\Json $encoder = null
    ) {
        $this->collectionFactory = $collectionFactory ?: ObjectManager::getInstance()
            ->get(\Magento\Variable\Model\ResourceModel\Variable\CollectionFactory::class);
        $this->storesVariables = $storesVariables ?: ObjectManager::getInstance()
            ->get(\Magento\Variable\Model\Source\Variables::class);
        $this->encoder = $encoder ?: ObjectManager::getInstance()
            ->get(\Magento\Framework\Serialize\Serializer\Json::class);
        $this->_url = $url;
        $this->_assetRepo = $assetRepo;
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
                    'onclick' => $onclickParts,
                    'class' => 'add-variable plugin',
                    'placeholders' => $this->getVariablesWysiwygData()
                ],
            ],
        ];
        $configPlugins = (array) $config->getData('plugins');
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
        return $this->_url->getUrl('mui/index/render', ['namespace' => 'variables_modal']);
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
     * Return variable related wysiwyg data
     *
     * @return bool|string
     */
    private function getVariablesWysiwygData()
    {
        $variablesData = array_merge(
            $this->getCustomVariables(),
            $this->getDefaultVariables()
        );
        return $this->encoder->serialize($variablesData);
    }
}

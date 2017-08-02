<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Model\Wysiwyg;

use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Ui\Component\Wysiwyg\ConfigInterface;

/**
 * Wysiwyg Config for Editor HTML Element
 *
 * @api
 * @since 2.0.0
 */
class Config extends \Magento\Framework\DataObject implements ConfigInterface
{
    /**
     * Wysiwyg status enabled
     */
    const WYSIWYG_ENABLED = 'enabled';

    /**
     * Wysiwyg status configuration path
     */
    const WYSIWYG_STATUS_CONFIG_PATH = 'cms/wysiwyg/enabled';

    /**
     *
     */
    const WYSIWYG_SKIN_IMAGE_PLACEHOLDER_ID = 'Magento_Cms::images/wysiwyg_skin_image.png';

    /**
     * Wysiwyg status hidden
     */
    const WYSIWYG_HIDDEN = 'hidden';

    /**
     * Wysiwyg status disabled
     */
    const WYSIWYG_DISABLED = 'disabled';

    /**
     * Wysiwyg image directory
     */
    const IMAGE_DIRECTORY = 'wysiwyg';

    /**
     * @var \Magento\Framework\AuthorizationInterface
     * @since 2.0.0
     */
    protected $_authorization;

    /**
     * @var \Magento\Framework\View\Asset\Repository
     * @since 2.0.0
     */
    protected $_assetRepo;

    /**
     * @var \Magento\Variable\Model\Variable\Config
     * @since 2.0.0
     */
    protected $_variableConfig;

    /**
     * @var \Magento\Widget\Model\Widget\Config
     * @since 2.0.0
     */
    protected $_widgetConfig;

    /**
     * Core event manager proxy
     *
     * @var \Magento\Framework\Event\ManagerInterface
     * @since 2.0.0
     */
    protected $_eventManager;

    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     * @since 2.0.0
     */
    protected $_scopeConfig;

    /**
     * @var array
     * @since 2.0.0
     */
    protected $_windowSize;

    /**
     * @var \Magento\Backend\Model\UrlInterface
     * @since 2.0.0
     */
    protected $_backendUrl;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     * @since 2.0.0
     */
    protected $_storeManager;

    /**
     * @var Filesystem
     * @since 2.1.0
     */
    protected $filesystem;

    /**
     * @param \Magento\Backend\Model\UrlInterface $backendUrl
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\AuthorizationInterface $authorization
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     * @param \Magento\Variable\Model\Variable\Config $variableConfig
     * @param \Magento\Widget\Model\Widget\Config $widgetConfig
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param Filesystem $filesystem
     * @param array $windowSize
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Backend\Model\UrlInterface $backendUrl,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\AuthorizationInterface $authorization,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Variable\Model\Variable\Config $variableConfig,
        \Magento\Widget\Model\Widget\Config $widgetConfig,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        Filesystem $filesystem,
        array $windowSize = [],
        array $data = []
    ) {
        $this->_backendUrl = $backendUrl;
        $this->_eventManager = $eventManager;
        $this->_scopeConfig = $scopeConfig;
        $this->_authorization = $authorization;
        $this->_assetRepo = $assetRepo;
        $this->_variableConfig = $variableConfig;
        $this->_widgetConfig = $widgetConfig;
        $this->_windowSize = $windowSize;
        $this->_storeManager = $storeManager;
        $this->filesystem = $filesystem;
        parent::__construct($data);
    }

    /**
     * Return Wysiwyg config as \Magento\Framework\DataObject
     *
     * Config options description:
     *
     * enabled:                 Enabled Visual Editor or not
     * hidden:                  Show Visual Editor on page load or not
     * use_container:           Wrap Editor contents into div or not
     * no_display:              Hide Editor container or not (related to use_container)
     * translator:              Helper to translate phrases in lib
     * files_browser_*:         Files Browser (media, images) settings
     * encode_directives:       Encode template directives with JS or not
     *
     * @param array|\Magento\Framework\DataObject $data Object constructor params to override default config values
     * @return \Magento\Framework\DataObject
     * @since 2.0.0
     */
    public function getConfig($data = [])
    {
        $config = new \Magento\Framework\DataObject();

        $config->setData(
            [
                'enabled' => $this->isEnabled(),
                'hidden' => $this->isHidden(),
                'use_container' => false,
                'add_variables' => true,
                'add_widgets' => true,
                'no_display' => false,
                'encode_directives' => true,
                'baseStaticUrl' => $this->_assetRepo->getStaticViewFileContext()->getBaseUrl(),
                'baseStaticDefaultUrl' => str_replace('index.php/', '', $this->_backendUrl->getBaseUrl())
                    . $this->filesystem->getUri(DirectoryList::STATIC_VIEW) . '/',
                'directives_url' => $this->_backendUrl->getUrl('cms/wysiwyg/directive'),
                'popup_css' => $this->_assetRepo->getUrl(
                    'mage/adminhtml/wysiwyg/tiny_mce/themes/advanced/skins/default/dialog.css'
                ),
                'content_css' => $this->_assetRepo->getUrl(
                    'mage/adminhtml/wysiwyg/tiny_mce/themes/advanced/skins/default/content.css'
                ),
                'width' => '100%',
                'height' => '500px',
                'plugins' => [],
            ]
        );

        $config->setData('directives_url_quoted', preg_quote($config->getData('directives_url')));

        if ($this->_authorization->isAllowed('Magento_Cms::media_gallery')) {
            $config->addData(
                [
                    'add_images' => true,
                    'files_browser_window_url' => $this->_backendUrl->getUrl('cms/wysiwyg_images/index'),
                    'files_browser_window_width' => $this->_windowSize['width'],
                    'files_browser_window_height' => $this->_windowSize['height'],
                ]
            );
        }

        if (is_array($data)) {
            $config->addData($data);
        }

        if ($config->getData('add_variables')) {
            $settings = $this->_variableConfig->getWysiwygPluginSettings($config);
            $config->addData($settings);
        }

        if ($config->getData('add_widgets')) {
            $settings = $this->_widgetConfig->getPluginSettings($config);
            $config->addData($settings);
        }

        return $config;
    }

    /**
     * Return path for skin images placeholder
     *
     * @return string
     * @since 2.0.0
     */
    public function getSkinImagePlaceholderPath()
    {
        $staticPath = $this->_storeManager->getStore()->getBaseStaticDir();
        $placeholderPath = $this->_assetRepo->createAsset(self::WYSIWYG_SKIN_IMAGE_PLACEHOLDER_ID)->getPath();
        return $staticPath . '/' . $placeholderPath;
    }

    /**
     * Check whether Wysiwyg is enabled or not
     *
     * @return bool
     * @since 2.0.0
     */
    public function isEnabled()
    {
        $wysiwygState = $this->_scopeConfig->getValue(
            self::WYSIWYG_STATUS_CONFIG_PATH,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
        return in_array($wysiwygState, [self::WYSIWYG_ENABLED, self::WYSIWYG_HIDDEN]);
    }

    /**
     * Check whether Wysiwyg is loaded on demand or not
     *
     * @return bool
     * @since 2.0.0
     */
    public function isHidden()
    {
        $status = $this->_scopeConfig->getValue(
            self::WYSIWYG_STATUS_CONFIG_PATH,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return $status == self::WYSIWYG_HIDDEN;
    }
}

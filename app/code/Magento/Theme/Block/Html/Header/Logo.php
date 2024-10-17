<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Theme\Block\Html\Header;

use Magento\Theme\ViewModel\Block\Html\Header\LogoPathResolverInterface;

/**
 * Logo page header block
 *
 * @api
 * @since 100.0.2
 */
class Logo extends \Magento\Framework\View\Element\Template
{
    /**
     * Current template name
     *
     * @var string
     */
    protected $_template = 'Magento_Theme::html/header/logo.phtml';

    /**
     * @var \Magento\MediaStorage\Helper\File\Storage\Database
     */
    protected $_fileStorageHelper;
    protected $_themeProvider;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\MediaStorage\Helper\File\Storage\Database $fileStorageHelper
     * @param \Magento\Framework\View\Design\Theme\ThemeProviderInterface $themeProvider
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\MediaStorage\Helper\File\Storage\Database $fileStorageHelper,
        \Magento\Framework\View\Design\Theme\ThemeProviderInterface $themeProvider,
        array $data = []
    ) {
        $this->_fileStorageHelper = $fileStorageHelper;
        $this->_themeProvider = $themeProvider;
        parent::__construct($context, $data);
    }

    /**
     * Check if current url is url for home page
     *
     * @deprecated 101.0.1 This function is no longer used. It was previously used by
     * Magento/Theme/view/frontend/templates/html/header/logo.phtml
     * to check if the logo should be clickable on the homepage.
     *
     * @return bool
     */
    public function isHomePage()
    {
        $currentUrl = $this->getUrl('', ['_current' => true]);
        $urlRewrite = $this->getUrl('*/*/*', ['_current' => true, '_use_rewrite' => true]);
        return $currentUrl == $urlRewrite;
    }

    /**
     * Get logo image URL
     *
     * @return string
     */
    public function getLogoSrc()
    {
        if (empty($this->_data['logo_src'])) {
            $this->_data['logo_src'] = $this->_getLogoUrl();
        }
        return $this->_data['logo_src'];
    }

    /**
     * Retrieve logo text
     *
     * @return string
     */
    public function getLogoAlt()
    {
        if (empty($this->_data['logo_alt'])) {
            $this->_data['logo_alt'] = $this->_scopeConfig->getValue(
                'design/header/logo_alt',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
        }
        return $this->_data['logo_alt'];
    }

    /**
     * Retrieve logo width
     *
     * @return int
     */
    public function getLogoWidth()
    {
        if (empty($this->_data['logo_width'])) {
            $this->_data['logo_width'] = $this->_scopeConfig->getValue(
                'design/header/logo_width',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
        }
        return (int)$this->_data['logo_width'];
    }

    /**
     * Retrieve logo height
     *
     * @return int
     */
    public function getLogoHeight()
    {
        if (empty($this->_data['logo_height'])) {
            $this->_data['logo_height'] = $this->_scopeConfig->getValue(
                'design/header/logo_height',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
        }
        return (int)$this->_data['logo_height'];
    }

    /**
     * Retrieve logo image URL
     *
     * @return string
     */
    protected function _getLogoUrl()
    {
        $path = null;
        /** @var LogoPathResolverInterface $logoPathResolver */
        $logoPathResolver = $this->getData('logoPathResolver');
        if ($logoPathResolver instanceof LogoPathResolverInterface) {
            $path = $logoPathResolver->getPath();
        }
        $logoUrl = $this->_urlBuilder
                ->getBaseUrl(['_type' => \Magento\Framework\UrlInterface::URL_TYPE_MEDIA]) . $path;

        if ($path !== null && $this->_isFile($path)) {
            $url = $logoUrl;
        } elseif ($this->getLogoFile()) {
            $url = $this->getViewFileUrl($this->getLogoFile());
        } else {
            $url = $this->getViewFileUrl('images/logo.svg');
        }
        return $url;
    }

    /**
     * If DB file storage is on - find there, otherwise - just file_exists
     *
     * @param string $filename relative path
     * @return bool
     */
    protected function _isFile($filename)
    {
        if ($this->_fileStorageHelper->checkDbUsage() && !$this->getMediaDirectory()->isFile($filename)) {
            $this->_fileStorageHelper->saveFileToFilesystem($filename);
        }

        return $this->getMediaDirectory()->isFile($filename);
    }

    /**
     * @return string|void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getThemeName()
    {
        $themeId = $this->_scopeConfig->getValue(
            \Magento\Framework\View\DesignInterface::XML_PATH_THEME_ID,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->_storeManager->getStore()->getId()
        );
        if (isset($themeId)) {
            $theme = $this->_themeProvider->getThemeById($themeId);
            return $theme->getThemeTitle();
        }
    }
}

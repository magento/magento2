<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Model;

use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\View\DesignInterface;
use Magento\Framework\View\Design\Theme\ThemeProviderInterface;
use Magento\Framework\App\Config\ValueInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Class ThemeValidator
 */
class ThemeValidator
{

    /**
     * Store Manager
     *
     * @var StoreManagerInterface $storeManager
     */
    private $storeManager;

    /**
     * Provider for themes registered in db
     *
     * @var ThemeProviderInterface $themeProvider
     */
    private $themeProvider;

    /**
     * Configuration Data
     *
     * @var ValueInterface $configData
     */
    private $configData;


    /**
     * @param StoreManagerInterface $storeManager
     * @param ThemeProviderInterface $themeProvider
     * @param ValueInterface $configData
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        ThemeProviderInterface $themeProvider,
        ValueInterface $configData
    )
    {
        $this->storeManager = $storeManager;
        $this->themeProvider = $themeProvider;
        $this->configData = $configData;
    }

    /**
     *
     * @param string[] $themePaths
     * @return array
     */
    public function validateIsThemeInUse($themePaths)
    {
        $messages = [];
        $themesById = [];
        foreach ($themePaths as $themePath) {
            $theme = $this->themeProvider->getThemeByFullPath($themePath);
            $themesById[$theme->getId()] = $themePath;
        }
        $configData = $this->configData
            ->getCollection()
            ->addFieldToFilter('path', DesignInterface::XML_PATH_THEME_ID)
            ->addFieldToFilter('value', ['in' => array_keys($themesById)]);
        foreach ($configData as $row) {
            switch($row['scope']) {
                case 'default':
                    $messages[] = $themesById[$row['value']] . ' is in use in default config';
                    break;
                case ScopeInterface::SCOPE_WEBSITES:
                    $messages[] = $themesById[$row['value']] . ' is in use in website '
                        . $this->storeManager->getWebsite($row['scope_id'])->getName();
                    break;
                case ScopeInterface::SCOPE_STORES:
                    $messages[] = $themesById[$row['value']] . ' is in use in website '
                        . $this->storeManager->getStore($row['scope_id'])->getName();
                    break;
            }
        }
        return $messages;
    }
}
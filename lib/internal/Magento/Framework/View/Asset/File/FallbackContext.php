<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Asset\File;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * An advanced context that contains information necessary for view files fallback system
 */
class FallbackContext extends Context
{
    /**
     * Secure path
     *
     * @deprecated
     */
    const SECURE_PATH = 'secure';

    /**
     * @var string
     */
    private $area;

    /**
     * @var string
     */
    private $theme;

    /**
     * @var string
     */
    private $locale;

    /**
     * @var bool
     *
     * @deprecated
     */
    private $isSecure;

    /**
     * @param string $baseUrl
     * @param string $areaType
     * @param string $themePath
     * @param string $localeCode
     * @param bool $isSecure
     */
    public function __construct($baseUrl, $areaType, $themePath, $localeCode, $isSecure = false)
    {
        $this->area = $areaType;
        $this->theme = $themePath;
        $this->locale = $localeCode;
        $this->isSecure = $isSecure;
        parent::__construct($baseUrl, DirectoryList::STATIC_VIEW, $this->generatePath());
    }

    /**
     * Get area code
     *
     * @return string
     */
    public function getAreaCode()
    {
        return $this->area;
    }

    /**
     * Get theme path
     *
     * @return string
     */
    public function getThemePath()
    {
        return $this->theme;
    }

    /**
     * Get locale code
     *
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Generate path based on the context parameters
     *
     * @return string
     */
    private function generatePath()
    {
        return $this->area .
            ($this->theme ? '/' . $this->theme : '') .
            ($this->locale ? '/' . $this->locale : '');
    }

    /**
     * Returns path to Require.js config object depending on HTTPS or HTTP protocol being used
     *
     * @return string
     */
    public function getConfigPath()
    {
        return $this->getPath();
    }
}

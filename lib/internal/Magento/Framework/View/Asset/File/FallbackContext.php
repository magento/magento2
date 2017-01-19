<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
     * @param string $baseUrl
     * @param string $areaType
     * @param string $themePath
     * @param string $localeCode
     */
    public function __construct($baseUrl, $areaType, $themePath, $localeCode)
    {
        $this->area = $areaType;
        $this->theme = $themePath;
        $this->locale = $localeCode;
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

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Asset\File;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * An advanced context that contains information necessary for view files fallback system
 *
 * @api
 * @since 2.0.0
 */
class FallbackContext extends Context
{
    /**
     * @var string
     * @since 2.0.0
     */
    private $area;

    /**
     * @var string
     * @since 2.0.0
     */
    private $theme;

    /**
     * @var string
     * @since 2.0.0
     */
    private $locale;

    /**
     * @param string $baseUrl
     * @param string $areaType
     * @param string $themePath
     * @param string $localeCode
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getAreaCode()
    {
        return $this->area;
    }

    /**
     * Get theme path
     *
     * @return string
     * @since 2.0.0
     */
    public function getThemePath()
    {
        return $this->theme;
    }

    /**
     * Get locale code
     *
     * @return string
     * @since 2.0.0
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Generate path based on the context parameters
     *
     * @return string
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getConfigPath()
    {
        return $this->getPath();
    }
}

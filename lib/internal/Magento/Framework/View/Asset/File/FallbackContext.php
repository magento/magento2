<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Framework\View\Asset\File;

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
        parent::__construct($baseUrl, \Magento\Framework\App\Filesystem::STATIC_VIEW_DIR, $this->generatePath());
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
    public function getLocaleCode()
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
        return $this->area . ($this->theme ? '/' . $this->theme : '') . ($this->locale ? '/' . $this->locale : '');
    }
}

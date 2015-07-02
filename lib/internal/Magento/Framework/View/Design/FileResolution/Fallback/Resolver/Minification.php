<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Design\FileResolution\Fallback\Resolver;

use Magento\Framework\View\Asset\ConfigInterface;
use Magento\Framework\View\Design\FileResolution\Fallback\ResolverInterface;
use Magento\Framework\View\Design\ThemeInterface;

/**
 * Resolver for minified asset, when minified is requested but not found
 */
class Minification implements ResolverInterface
{
    /**
     * @var ResolverInterface
     */
    protected $fallback;

    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * @var array
     */
    protected $extensions;

    /**
     * @param ResolverInterface $fallback
     * @param ConfigInterface $config
     */
    public function __construct(ResolverInterface $fallback, ConfigInterface $config)
    {
        $this->fallback = $fallback;
        $this->config = $config;
    }
    /**
     * Get path of file after using fallback rules
     *
     * @param string $type
     * @param string $file
     * @param string|null $area
     * @param ThemeInterface|null $theme
     * @param string|null $locale
     * @param string|null $module
     * @return string|false
     */
    public function resolve($type, $file, $area = null, ThemeInterface $theme = null, $locale = null, $module = null)
    {
        $path = $this->fallback->resolve($type, $file, $area, $theme, $locale, $module);
        $extension = pathinfo($file, PATHINFO_EXTENSION);
        if (
            !$path &&
            $this->config->isAssetMinification($extension) &&
            substr($file, -5 - strlen($extension)) == '.min.' . $extension
        ) {
            $newFile = substr($file, 0, -4 - strlen($extension)) . $extension;
            $path = $this->fallback->resolve($type, $newFile, $area, $theme, $locale, $module);
        }
        return $path;
    }
}

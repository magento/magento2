<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Asset\PreProcessor\AlternativeSource;

use Magento\Framework\View\Asset\File;
use Magento\Framework\View\Asset\Repository;

/**
 * Class AssetBuilder
 * @since 2.0.0
 */
class AssetBuilder
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
     * @var string
     * @since 2.0.0
     */
    private $module;

    /**
     * @var string
     * @since 2.0.0
     */
    private $path;

    /**
     * @var Repository
     * @since 2.0.0
     */
    private $repository;

    /**
     * Constructor
     *
     * @param Repository $repository
     * @since 2.0.0
     */
    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Set area
     *
     * @param string $area
     * @return $this
     * @since 2.0.0
     */
    public function setArea($area)
    {
        $this->area = $area;
        return $this;
    }

    /**
     * Set theme
     *
     * @param string $theme
     * @return $this
     * @since 2.0.0
     */
    public function setTheme($theme)
    {
        $this->theme = $theme;
        return $this;
    }

    /**
     * Set locale
     *
     * @param string $locale
     * @return $this
     * @since 2.0.0
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
        return $this;
    }

    /**
     * Set module
     *
     * @param string $module
     * @return $this
     * @since 2.0.0
     */
    public function setModule($module)
    {
        $this->module = $module;
        return $this;
    }

    /**
     * Set path
     *
     * @param string $path
     * @return $this
     * @since 2.0.0
     */
    public function setPath($path)
    {
        $this->path = $path;
        return $this;
    }

    /**
     * @return File
     * @since 2.0.0
     */
    public function build()
    {
        $params = [
            'area' => $this->area,
            'theme' => $this->theme,
            'locale' => $this->locale,
            'module' => $this->module,
        ];

        $asset = $this->repository->createAsset($this->path, $params);

        unset($this->path, $this->module, $this->locale, $this->theme, $this->area);

        return $asset;
    }
}

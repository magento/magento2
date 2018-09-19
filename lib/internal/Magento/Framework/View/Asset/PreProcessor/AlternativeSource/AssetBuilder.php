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
 */
class AssetBuilder
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
     * @var string
     */
    private $module;

    /**
     * @var string
     */
    private $path;

    /**
     * @var Repository
     */
    private $repository;

    /**
     * Constructor
     *
     * @param Repository $repository
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
     */
    public function setPath($path)
    {
        $this->path = $path;
        return $this;
    }

    /**
     * @return File
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

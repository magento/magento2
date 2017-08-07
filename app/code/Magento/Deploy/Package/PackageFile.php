<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Package;

use Magento\Framework\View\Asset;
use Magento\Framework\View\Asset\Repository;

/**
 * Deployment Package File class
 * @since 2.2.0
 */
class PackageFile extends Asset
{
    /**
     * @var Package
     * @since 2.2.0
     */
    private $package;

    /**
     * @var Package
     * @since 2.2.0
     */
    private $origPackage;

    /**
     * @var string
     * @since 2.2.0
     */
    private $deployedFileName;

    /**
     * @var string
     * @since 2.2.0
     */
    private $deployedFilePath;

    /**
     * @var string
     * @since 2.2.0
     */
    private $content;

    /**
     * @param Package $package
     * @return bool
     * @since 2.2.0
     */
    public function setPackage(Package $package)
    {
        $this->package = $package;
        if ($this->origPackage === null) {
            $this->origPackage = $package;
        }

        $package->addFile($this);
        $package->addFileToMap($this);

        return true;
    }

    /**
     * @return Package
     * @since 2.2.0
     */
    public function getPackage()
    {
        return $this->package;
    }

    /**
     * @return Package
     * @since 2.2.0
     */
    public function getOrigPackage()
    {
        return $this->origPackage;
    }

    /**
     * @param string $name
     * @return bool
     * @since 2.2.0
     */
    public function setDeployedFileName($name)
    {
        $this->deployedFileName = $name;
        return true;
    }

    /**
     * @return string
     * @since 2.2.0
     */
    public function getDeployedFileName()
    {
        return $this->deployedFileName;
    }

    /**
     * @param string $name
     * @return bool
     * @since 2.2.0
     */
    public function setDeployedFilePath($name)
    {
        $this->deployedFilePath = $name;
        return true;
    }

    /**
     * @return string
     * @since 2.2.0
     */
    public function getDeployedFilePath()
    {
        return $this->deployedFilePath;
    }

    /**
     * @return string
     * @since 2.2.0
     */
    public function getDeployedFileId()
    {
        if ($this->getModule()) {
            return $this->getModule() . Repository::FILE_ID_SEPARATOR . $this->getDeployedFileName();
        }
        return $this->getDeployedFileName();
    }

    /**
     * @return string
     * @since 2.2.0
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param string $content
     * @return bool
     * @since 2.2.0
     */
    public function setContent($content)
    {
        $this->content = $content;
        return true;
    }

    /**
     * @param string $area
     * @return bool
     * @since 2.2.0
     */
    public function setArea($area)
    {
        $this->area = $area;
        return true;
    }

    /**
     * @param string $theme
     * @return bool
     * @since 2.2.0
     */
    public function setTheme($theme)
    {
        $this->theme = $theme;
        return true;
    }

    /**
     * @param string $locale
     * @return bool
     * @since 2.2.0
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
        return true;
    }

    /**
     * @param string $module
     * @return bool
     * @since 2.2.0
     */
    public function setModule($module)
    {
        $this->module = $module;
        return true;
    }
}

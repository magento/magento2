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
 */
class PackageFile extends Asset
{
    /**
     * @var Package
     */
    private $package;

    /**
     * @var Package
     */
    private $origPackage;

    /**
     * @var string
     */
    private $deployedFileName;

    /**
     * @var string
     */
    private $deployedFilePath;

    /**
     * @var string
     */
    private $content;

    /**
     * @param Package $package
     * @return bool
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
     */
    public function getPackage()
    {
        return $this->package;
    }

    /**
     * @return Package
     */
    public function getOrigPackage()
    {
        return $this->origPackage;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function setDeployedFileName($name)
    {
        $this->deployedFileName = $name;
        return true;
    }

    /**
     * @return string
     */
    public function getDeployedFileName()
    {
        return $this->deployedFileName;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function setDeployedFilePath($name)
    {
        $this->deployedFilePath = $name;
        return true;
    }

    /**
     * @return string
     */
    public function getDeployedFilePath()
    {
        return $this->deployedFilePath;
    }

    /**
     * @return string
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
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param string $content
     * @return bool
     */
    public function setContent($content)
    {
        $this->content = $content;
        return true;
    }

    /**
     * @param string $area
     * @return bool
     */
    public function setArea($area)
    {
        $this->area = $area;
        return true;
    }

    /**
     * @param string $theme
     * @return bool
     */
    public function setTheme($theme)
    {
        $this->theme = $theme;
        return true;
    }

    /**
     * @param string $locale
     * @return bool
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
        return true;
    }

    /**
     * @param string $module
     * @return bool
     */
    public function setModule($module)
    {
        $this->module = $module;
        return true;
    }
}

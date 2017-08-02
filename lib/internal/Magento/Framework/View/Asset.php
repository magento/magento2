<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View;

use Magento\Framework\View\Asset\Repository;

/**
 * Class \Magento\Framework\View\Asset
 *
 * @since 2.2.0
 */
class Asset
{
    /**
     * @var string
     * @since 2.2.0
     */
    private $fileName;

    /**
     * @var string
     * @since 2.2.0
     */
    private $sourcePath;

    /**
     * @var string
     * @since 2.2.0
     */
    protected $module;

    /**
     * @var string
     * @since 2.2.0
     */
    protected $area;

    /**
     * @var string
     * @since 2.2.0
     */
    protected $theme;

    /**
     * @var string
     * @since 2.2.0
     */
    protected $locale;

    /**
     * @var string
     * @since 2.2.0
     */
    private $extension;

    /**
     * File constructor.
     * @param string $fileName
     * @param string $sourcePath
     * @param string|null $area
     * @param string|null $theme
     * @param string|null $locale
     * @param string|null $module
     * @since 2.2.0
     */
    public function __construct(
        $fileName,
        $sourcePath = null,
        $area = null,
        $theme = null,
        $locale = null,
        $module = null
    ) {
        $this->fileName = $fileName;
        $this->sourcePath = $sourcePath;
        $this->module = $module;
        $this->area = $area;
        $this->theme = $theme;
        $this->locale = $locale;
    }

    /**
     * @return string
     * @since 2.2.0
     */
    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     * @return string
     * @since 2.2.0
     */
    public function getFileId()
    {
        if ($this->getModule()) {
            return $this->getModule() . Repository::FILE_ID_SEPARATOR . $this->getFileName();
        }
        return $this->getFileName();
    }

    /**
     * @return string
     * @since 2.2.0
     */
    public function getFilePath()
    {
        if ($this->getModule()) {
            return $this->getModule() . '/' . $this->getFileName();
        }
        return $this->getFileName();
    }

    /**
     * @return string
     * @since 2.2.0
     */
    public function getSourcePath()
    {
        return $this->sourcePath;
    }

    /**
     * @return string
     * @since 2.2.0
     */
    public function getModule()
    {
        return $this->module;
    }

    /**
     * @return string
     * @since 2.2.0
     */
    public function getArea()
    {
        return $this->area;
    }

    /**
     * @return string
     * @since 2.2.0
     */
    public function getTheme()
    {
        return $this->theme;
    }

    /**
     * @return string
     * @since 2.2.0
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @return string
     * @since 2.2.0
     */
    public function getExtension()
    {
        if (!$this->extension) {
            $this->extension = strtolower(pathinfo($this->getFileName(), PATHINFO_EXTENSION));
        }
        return $this->extension;
    }
}

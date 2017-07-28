<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\File\Collector;

use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Component\DirSearch;
use Magento\Framework\View\Design\ThemeInterface;
use Magento\Framework\View\File\CollectorInterface;
use Magento\Framework\View\File\Factory as FileFactory;

/**
 * Source of base files introduced by modules
 * @since 2.0.0
 */
class Base implements CollectorInterface
{
    /**
     * @var DirSearch
     * @since 2.0.0
     */
    protected $componentDirSearch;

    /**
     * @var string
     * @since 2.0.0
     */
    private $subDir;

    /**
     * @var FileFactory
     * @since 2.0.0
     */
    private $fileFactory;

    /**
     * Constructor
     *
     * @param DirSearch $dirSearch
     * @param FileFactory $fileFactory
     * @param string $subDir
     * @since 2.0.0
     */
    public function __construct(
        DirSearch $dirSearch,
        FileFactory $fileFactory,
        $subDir = ''
    ) {
        $this->componentDirSearch = $dirSearch;
        $this->fileFactory = $fileFactory;
        $this->subDir = $subDir ? $subDir . '/' : '';
    }

    /**
     * Retrieve files
     *
     * @param \Magento\Framework\View\Design\ThemeInterface $theme
     * @param string $filePath
     * @return \Magento\Framework\View\File[]
     * @since 2.0.0
     */
    public function getFiles(ThemeInterface $theme, $filePath)
    {
        $result = [];
        $sharedFiles = $this->componentDirSearch->collectFilesWithContext(
            ComponentRegistrar::MODULE,
            "view/base/{$this->subDir}{$filePath}"
        );
        foreach ($sharedFiles as $file) {
            $result[] = $this->fileFactory->create($file->getFullPath(), $file->getComponentName(), null, true);
        }
        $area = $theme->getData('area');
        $themeFiles = $this->componentDirSearch->collectFilesWithContext(
            ComponentRegistrar::MODULE,
            "view/{$area}/{$this->subDir}{$filePath}"
        );
        foreach ($themeFiles as $file) {
            $result[] = $this->fileFactory->create($file->getFullPath(), $file->getComponentName());
        }
        return $result;
    }
}

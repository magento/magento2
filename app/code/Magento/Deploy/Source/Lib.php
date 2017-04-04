<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Source;

use Magento\Deploy\Package\Package;
use Magento\Framework\App\Utility\Files;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Deploy\Package\PackageFileFactory;

/**
 * Collect files eligible for deployment from library
 *
 * Provides files collected from web library
 */
class Lib implements SourceInterface
{
    const TYPE = 'lib';

    /**
     * @var Files
     */
    private $filesUtil;

    /**
     * @var Filesystem\Directory\WriteInterface
     */
    private $libDir;

    /**
     * @var PackageFileFactory
     */
    private $packageFileFactory;

    /**
     * Lib constructor
     *
     * @param Files $filesUtil
     * @param Filesystem $filesystem
     * @param PackageFileFactory $packageFileFactory
     */
    public function __construct(
        Files $filesUtil,
        Filesystem $filesystem,
        PackageFileFactory $packageFileFactory
    ) {
        $this->filesUtil = $filesUtil;
        $this->libDir = $filesystem->getDirectoryWrite(DirectoryList::LIB_WEB);
        $this->packageFileFactory = $packageFileFactory;
    }

    /**
     * @inheritdoc
     */
    public function get()
    {
        $files = [];
        foreach ($this->filesUtil->getStaticLibraryFiles() as $fileName) {
            if (strpos($fileName, 'css/docs') === 0) {
                continue;
            }
            $fullPath = $this->libDir->getAbsolutePath($fileName);
            $params = [
                'area' => Package::BASE_AREA,
                'theme' => null,
                'locale' => null,
                'module' => null,
                'fileName' => $fileName,
                'sourcePath' => $fullPath
            ];
            $files[] = $this->packageFileFactory->create($params);
        }
        return $files;
    }
}

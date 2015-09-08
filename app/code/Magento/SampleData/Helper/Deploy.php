<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SampleData\Helper;

use Magento\Framework\App\Filesystem\DirectoryList;
use SebastianBergmann\Exporter\Exception;

class Deploy
{
    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList
     */
    private $directoryList;

    /**
     * @param DirectoryList $directoryList
     */
    public function __construct(DirectoryList $directoryList)
    {
        $this->directoryList = $directoryList;
    }

    /**
     * Launch deploy media process
     *
     * @throws \Exception
     * @return void
     */
    public function run()
    {
        $vendorsMagentoMedia = $this->getVendorsMagentoMediaPath();
        if ($vendorsMagentoMedia) {
            $mediaDir = $this->directoryList->getPath(DirectoryList::MEDIA);
            $this->copyAll($vendorsMagentoMedia, $mediaDir, ['/composer.json', '/.git']);
        }
    }

    /**
     * Check presense sample
     *
     * @return bool
     */
    public function isMediaPresent()
    {
        return $this->getVendorsMagentoMediaPath() !== null;
    }

    /**
     * Get Vendors Path for sample-data-media package
     *
     * @return string
     */
    protected function getVendorsMagentoMediaPath()
    {
        $vendorPathConfig = $this->directoryList->getPath(DirectoryList::CONFIG) . '/vendor_path.php';
        if (!file_exists($vendorPathConfig)) {
            return null;
        }
        $vendorPath = include $vendorPathConfig;
        $vendorsMagentoDir = $this->directoryList->getPath(DirectoryList::ROOT) . '/' . $vendorPath . '/magento';
        if (!file_exists($vendorsMagentoDir)) {
            return null;
        }
        $vendorsMagentoMedia = $vendorsMagentoDir . '/sample-data-media';
        if (file_exists($vendorsMagentoMedia)) {
            return $vendorsMagentoMedia;
        }
        return null;
    }

    /**
     * Copy all files maintaining the directory structure except excluded
     *
     * @param string $from
     * @param string $to
     * @param array $exclude
     * @return void
     */
    protected function copyAll($from, $to, $exclude = [])
    {
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($from));
        /** @var \SplFileInfo $file */
        foreach ($iterator as $file) {
            if (!$file->isDir()) {
                $source = $file->getPathname();
                $relative = substr($source, strlen($from));
                if ($this->isExcluded($relative, $exclude)) {
                    continue;
                }
                $target = $to . $relative;
                if (file_exists($target) && md5_file($source) == md5_file($target)) {
                    continue;
                }
                $targetDir = dirname($target);
                if (!is_dir($targetDir)) {
                    mkdir($targetDir, 0755, true);
                }
                copy($source, $target);
            }
        }
    }

    /**
     * @param string $path
     * @param array $exclude
     * @return bool
     */
    protected function isExcluded($path, $exclude)
    {
        $pathNormalized = str_replace('\\', '/', $path);

        foreach ($exclude as $item) {
            if (strpos($pathNormalized, $item) !== false) {
                return true;
            }
        }

        return false;
    }
}

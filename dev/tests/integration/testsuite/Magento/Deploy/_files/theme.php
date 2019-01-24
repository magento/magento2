<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
use Magento\Framework\Component\ComponentRegistrar;

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/* @var \Magento\Framework\Filesystem $filesystem */
$filesystem = $objectManager->get(\Magento\Framework\Filesystem::class);
$appDir = $filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::APP);

if (!function_exists('rcopy')) {
    /**
     * Recursively copy files from one directory to another
     *
     * @param string $src - Source of files being moved
     * @param string $destination - Destination of files being moved
     * @return bool
     */
    function rcopy($src, $destination)
    {
        // If source is not a directory stop processing
        if (!is_dir($src)) {
            return false;
        }

        // If the destination directory does not exist create it
        // If the destination directory could not be created stop processing
        if (!is_dir($destination)) {
            if (!mkdir($destination)) {
                return false;
            }
        }
        // Open the source directory to read in files
        $iterator = new DirectoryIterator($src);
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                copy($file->getRealPath(), $destination . '/' . $file->getFilename());
            } else if (!$file->isDot() && $file->isDir()) {
                rcopy($file->getRealPath(), $destination . '/' . $file);
            }
        }
    }
}

/** @var ComponentRegistrar $registrar */
$registrar = $objectManager->get(ComponentRegistrar::class);

//rcopy(
//    __DIR__ . '/zoom1',
//    $appDir->getAbsolutePath() . 'design/frontend/Magento/zoom1'
//);
if (!$registrar->getPath(ComponentRegistrar::THEME, 'frontend/Magento/zoom1')) {
    ComponentRegistrar::register(
        ComponentRegistrar::THEME,
        'frontend/Magento/zoom1',
        __DIR__ . '/zoom1'
    );
}

//rcopy(
//    __DIR__ . '/zoom2',
//    $appDir->getAbsolutePath() . 'design/frontend/Magento/zoom2'
//);
if (!$registrar->getPath(ComponentRegistrar::THEME, 'frontend/Magento/zoom2')) {
    ComponentRegistrar::register(
        ComponentRegistrar::THEME,
        'frontend/Magento/zoom2',
        __DIR__ . '/zoom2'
    );
}

//rcopy(
//    __DIR__ . '/zoom3',
//    $appDir->getAbsolutePath() . 'design/frontend/Magento/zoom3'
//);
if (!$registrar->getPath(ComponentRegistrar::THEME, 'frontend/Magento/zoom3')) {
    ComponentRegistrar::register(
        ComponentRegistrar::THEME,
        'frontend/Magento/zoom3',
        __DIR__ . '/zoom3'
    );
}

/** @var \Magento\Theme\Model\Theme\Registration $themeRegistration */
$themeRegistration = $objectManager->get(\Magento\Theme\Model\Theme\Registration::class);
$themeRegistration->register();

<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestFramework\Component;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;

class ThemeRegistration
{
    /**
     * Register themes under specified directory
     *
     * Each theme should contain registration.php file, which uses ComponentRegistrar to register the theme
     *
     * @param string $dir
     * @return void
     */
    public static function registerThemesInDir($dir)
    {
        $iterator = new RegexIterator(
            new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS)
            ),
            '/^.+\/registration\.php$/'
        );
        /** @var \SplFileInfo $registrationFile */
        foreach ($iterator as $registrationFile) {
            require_once $registrationFile->getRealPath();
        }

        /** @var $registration \Magento\Theme\Model\Theme\Registration */
        $registration = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Theme\Model\Theme\Registration'
        );

        $registration->register();
    }
}

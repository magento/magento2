<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestFramework\Component;

class ThemeRegistration
{
    /**
     * Register themes under specified directory
     *
     * Themes should be located on the 3rd level under the directory (<area>/<Vendor>/<name>/<theme files>)
     * Each theme should contain registration.php file, which uses ComponentRegistrar to register the theme
     *
     * @param string $dir
     * @return void
     */
    public static function registerThemesInDir($dir)
    {
        $themeRegistrationFiles = glob($dir . '/*/*/*/registration.php');
        foreach ($themeRegistrationFiles as $registrationFile) {
            require_once $registrationFile;
        }

        /** @var $registration \Magento\Theme\Model\Theme\Registration */
        $registration = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Theme\Model\Theme\Registration'
        );

        $registration->register();
    }
}

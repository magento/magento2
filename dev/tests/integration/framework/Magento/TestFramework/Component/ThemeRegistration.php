<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestFramework\Component;

class ThemeRegistration
{
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

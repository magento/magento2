<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * A helper class.
 * Goes through non-composer components under app/code looking for their *-registration.php files and
 * executes them to get these components registered with Magento framework.
 */
class NonComposerComponentRegistrar 
{
    /**
     * Instance of NonComposerComponentRegistrar class
     *
     * @var NonComposerComponentRegistrar instance
     */
    private static $instance = null;

    /**
     * Regex to filter the supported registration file types
     */
    const REGISTRATION_FILES_REGEX =
        '/module-registration\.php$|library-registration\.php$|theme-registration\.php$|language-registration\.php$/';

    /**
     * Public static method to access the class instance
     * @return NonComposerComponentRegistrar
     */
    public static function Instance() {
        if (static::$instance === null) {
            static::$instance = new NonComposerComponentRegistrar();
        }
        return static::$instance;
    }

    /**
     * Find the registration files under 'app/code' and execute them. So that, these non-composer installed
     * components are registered within the framework.
     *
     * @return void
     */
    public function Register() {

        $recDirItr = new \RecursiveDirectoryIterator(
            dirname(dirname(__FILE__)) . '/code',
            \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::FOLLOW_SYMLINKS
        );
        $recItrItr = new \RecursiveIteratorIterator($recDirItr);
        $dirItr = new \RegexIterator($recItrItr, self::REGISTRATION_FILES_REGEX);

        // Go through each registration file and execute it so that all the non-components
        // get registered properly
        foreach ($dirItr as $fileInfo) {
            $fullFileName = $fileInfo->getPathname();
            include $fullFileName;
        }
    }
}

// Register all the non-composer components.
NonComposerComponentRegistrar::Instance()->Register();
?>

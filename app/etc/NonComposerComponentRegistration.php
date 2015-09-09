<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * A helper class.
 * Goes through non-composer components under:
 * 1. app/code
 * 2. app/design
 * 3. app/i18n and
 * 4. lib/internal/Magento/Framework
 * looking for their registration.php files and executes them to get these components registered with Magento
 * framework.
 */

class NonComposerComponentRegistration
{
    /**
     * Instance of NonComposerComponentRegistration class
     *
     * @var NonComposerComponentRegistration instance
     */
    private static $instance = null;

     /**
     * The list of supported non-composer component paths
     *
     * @var string array $pathList
     */
    private static $pathList;

    /**
     * private constructor.
     */
    private function __construct()
    {
        // The supported directories are:
        // 1. app/code
        // 2. app/design
        // 3. app/i18n
        // 4. lib/internal
        static::$pathList[ ] = dirname(dirname(__FILE__)) . '/code/*/*/registration.php';
        static::$pathList[ ] = dirname(dirname(__FILE__)) . '/design/*/*/*/registration.php';
        static::$pathList[ ] = dirname(dirname(__FILE__)) . '/i18n/*/*/registration.json';
        static::$pathList[ ] = dirname(dirname(__FILE__)) . '/lib/internal/*/*/registration.json';
    }

    /**
     * Public static method to access the class instance
     *
     * @return NonComposerComponentRegistration
     */
    public static function getInstance()
    {
        if (static::$instance === null) {
            static::$instance = new NonComposerComponentRegistration();
        }
        return static::$instance;
    }

    /**
     * Find the registration files and execute them. So that, these non-composer installed
     * components are registered within the framework.
     *
     * @return void
     */
    public function register()
    {
        foreach (static::$pathList as $path) {
            // Sorting is disabled intentionally for performance improvement
            $files = glob($path, GLOB_NOSORT);
            if ( $files === false ) {
                throw new \RuntimeException('glob() returned error while searching in \'' . $path . '\'');
            }
            foreach ($files as $file) {
                include $file;
            }
        }
    }
}

// Register all the non-composer components.
NonComposerComponentRegistration::getInstance()->register();
?>

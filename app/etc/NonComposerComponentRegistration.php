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
     * Regex to filter the supported registration file types
     */
    const REGISTRATION_FILES_REGEX = '/registration\.php$/';

    /**
     * The list of directories and max recursion depth while searching for registration.php files
     *
     * @var string array $directoryList
     */
    private static $directoryList;

    /**
     * private constructor.
     */
    private function __construct()
    {
        // The supported directories are:
        // 1. app/code => max recursion depth: 2
        // 2. app/design => max recursion depth: 3
        // 3. app/i18n => max recursion depth: 2
        // 4. lib/internal => max recursion depth: 2
        static::$directoryList[dirname(dirname(__FILE__)) . '/code'] = 2;
        static::$directoryList[dirname(dirname(__FILE__)) . '/design'] = 3;
        static::$directoryList[dirname(dirname(__FILE__)) . '/i18n'] = 2;
        static::$directoryList[dirname(dirname(dirname(__FILE__))) . '/lib/internal'] = 2;
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

        foreach (static::$directoryList as $directory => $maxDepth) {
            $recDirItr = new \RecursiveDirectoryIterator(
                $directory,
                \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::FOLLOW_SYMLINKS
            );
            $recItrItr = new \RecursiveIteratorIterator($recDirItr);
            $recItrItr->setMaxDepth($maxDepth);
            $dirItr = new \RegexIterator($recItrItr, self::REGISTRATION_FILES_REGEX);

            // Go through each registration file and execute it so that all the non-components
            // get registered properly
            foreach ($dirItr as $fileInfo) {
                $fullFileName = $fileInfo->getPathname();
                include $fullFileName;
            }
        }
    }
}

// Register all the non-composer components.
NonComposerComponentRegistration::getInstance()->register();
?>

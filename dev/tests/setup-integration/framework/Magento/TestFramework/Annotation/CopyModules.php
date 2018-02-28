<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\TestFramework\Annotation;

use Magento\Framework\Filesystem\Io\File;
use Magento\TestFramework\Deploy\CliCommand;
use Magento\TestFramework\Deploy\TestModuleManager;

/**
 * Handler for applying reinstallMagento annotation.
 */
class CopyModules
{
    /**
     * @var TestModuleManager
     */
    private $moduleManager;

    /**
     * @var CliCommand
     */
    private $cliCommand;

    /**
     * CopyModules constructor.
     */
    public function __construct()
    {
        $this->moduleManager = new TestModuleManager();
        $this->cliCommand = new CliCommand($this->moduleManager);
    }

    /**
     * Handler for 'startTest' event.
     *
     * @param  \PHPUnit\Framework\TestCase $test
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function startTest(\PHPUnit\Framework\TestCase $test)
    {
        $annotations = $test->getAnnotations();
        //This annotation can be declared only on method level
        if (isset($annotations['method']['moduleName'])) {
            $moduleName = $annotations['method']['moduleName'][0];
            $this->cliCommand->introduceModule($moduleName);
            $path = MAGENTO_MODULES_PATH . explode("_", $moduleName)[1] . '/registration.php';
            include_once $path;
        }
    }

    /**
     * Handler for 'startTest' event
     *
     * @param \PHPUnit\Framework\TestCase $test
     */
    public function endTest(\PHPUnit\Framework\TestCase $test)
    {
        $annotations = $test->getAnnotations();
        //This annotation can be declared only on method level
        if (isset($annotations['method']['moduleName'])) {
            $path = MAGENTO_MODULES_PATH .
                //Take only module name from Magento_ModuleName
                explode("_", $annotations['method']['moduleName'][0])[1];

            File::rmdirRecursive($path);
        }
    }
}

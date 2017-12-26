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
 * Handler for applying reinstallMagento annotation
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
     * Handler for 'startTest' event
     *
     * @param  \PHPUnit\Framework\TestCase $test
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function startTest(\PHPUnit\Framework\TestCase $test)
    {
        $annotations = $test->getAnnotations();
        //This annotation can be declared only on method level
        if (isset($annotations['method']['moduleName'])) {
            $this->cliCommand->introduceModule($annotations['method']['moduleName'][0]);
        }
    }
}

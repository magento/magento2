<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\TestFramework\Annotation;

use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Filesystem\Io\File;
use Magento\TestFramework\Deploy\CliCommand;
use Magento\TestFramework\Deploy\TestModuleManager;
use PHPUnit\Util\Test as TestUtil;

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
        $annotations = TestCaseAnnotation::getInstance()->getAnnotations($test);
        //This annotation can be declared only on method level
        if (isset($annotations['method']['moduleName'])) {
            $moduleNames = $annotations['method']['moduleName'];

            foreach ($moduleNames as $moduleName) {
                $this->cliCommand->introduceModule($moduleName);
                //Include module`s registration.php to load it
                $path = MAGENTO_MODULES_PATH . explode("_", $moduleName)[1] . '/registration.php';
                include $path;
            }
        }
    }

    /**
     * Handler for 'startTest' event
     *
     * @param \PHPUnit\Framework\TestCase $test
     */
    public function endTest(\PHPUnit\Framework\TestCase $test)
    {
        $annotations = TestCaseAnnotation::getInstance()->getAnnotations($test);
        //This annotation can be declared only on method level
        if (!empty($annotations['method']['moduleName'])) {
            foreach ($annotations['method']['moduleName'] as $moduleName) {
                $path = MAGENTO_MODULES_PATH .
                    //Take only module name from Magento_ModuleName
                    explode("_", $moduleName)[1];
                File::rmdirRecursive($path);
                $this->unsergisterModuleFromComponentRegistrar($moduleName);
            }
        }
    }

    /**
     * Unregister module from component registrar.
     * The component registrar uses static private variable and does not provide unregister method,
     * however unregister is required to remove registered modules after they are deleted from app/code.
     *
     * @param string $moduleName
     *
     * @return void
     */
    private function unsergisterModuleFromComponentRegistrar($moduleName)
    {
        $reflection = new \ReflectionClass(ComponentRegistrar::class);
        $reflectionProperty = $reflection->getProperty('paths');
        $reflectionProperty->setAccessible(true);
        $value = $reflectionProperty->getValue();
        unset($value[ComponentRegistrar::MODULE][$moduleName]);
        $reflectionProperty->setValue(null, $value);
    }
}

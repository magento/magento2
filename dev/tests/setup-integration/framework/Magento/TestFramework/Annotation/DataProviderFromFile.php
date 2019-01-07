<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\TestFramework\Annotation;

use Magento\TestFramework\Deploy\CliCommand;
use Magento\TestFramework\Deploy\TestModuleManager;
use Magento\TestFramework\TestCase\MutableDataInterface;

/**
 * Handler for applying reinstallMagento annotation.
 */
class DataProviderFromFile
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
     * Start test.
     *
     * @param \PHPUnit\Framework\TestCase $test
     * @throws \Exception
     */
    public function startTest(\PHPUnit\Framework\TestCase $test)
    {
        $annotations = $test->getAnnotations();
        //This annotation can be declared only on method level
        if (isset($annotations['method']['dataProviderFromFile']) && $test instanceof MutableDataInterface) {
            $data = include TESTS_MODULES_PATH . "/" . $annotations['method']['dataProviderFromFile'][0];
            $test->setData($data);
        } else if (!$test instanceof MutableDataInterface) {
            throw new \Exception("Test type do not supports @dataProviderFromFile annotation");
        }
    }

    /**
     * Finish test.
     *
     * @param \PHPUnit\Framework\TestCase $test
     * @throws \Exception
     */
    public function endTest(\PHPUnit\Framework\TestCase $test)
    {
        if ($test instanceof MutableDataInterface) {
            $test->flushData();
        }
    }
}

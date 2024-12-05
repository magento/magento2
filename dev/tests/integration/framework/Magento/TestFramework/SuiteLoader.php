<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework;

use Magento\TestFramework\Workaround\Override\Config;
use Magento\TestFramework\Workaround\Override\WrapperGenerator;
use PHPUnit\Runner\TestSuiteLoader;

/**
 * Custom suite loader for adding wrapper for tests.
 */
class SuiteLoader
{
    /**
     * @var TestSuiteLoader
     */
    private $suiteLoader;

    /**
     * @var WrapperGenerator
     */
    private $generator;

    /**
     * @var Config
     */
    private $testsConfig;

    /**
     * SuiteLoader constructor.
     */
    public function __construct()
    {
        $this->suiteLoader = new TestSuiteLoader();
        $this->generator = new WrapperGenerator();
        $this->testsConfig = Config::getInstance();
    }

    /**
     * @inheritdoc
     */
    public function load(string $suiteClassFile): \ReflectionClass
    {
        $resultClass = $this->suiteLoader->load($suiteClassFile);

        if ($this->testsConfig->hasSkippedTest($resultClass->getName())
            && !in_array(SkippableInterface::class, $resultClass->getInterfaceNames(), true)
        ) {
            $resultClass = new \ReflectionClass($this->generator->generateTestWrapper($resultClass));
        }

        return $resultClass;
    }

    /**
     * @inheritdoc
     */
    public function reload(\ReflectionClass $aClass): \ReflectionClass
    {
        return $aClass;
    }
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento;

use Magento\TestFramework\SkippableInterface;
use Magento\TestFramework\Workaround\Override\Config;
use Magento\TestFramework\Workaround\Override\WrapperGenerator;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Util\Configuration;

/**
 * Integration tests wrapper.
 */
class IntegrationTest extends TestSuite
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param string $className
     * @return TestSuite
     */
    public static function suite($className)
    {
        $generator = new WrapperGenerator();
        $overrideConfig = Config::getInstance();
        $configuration = Configuration::getInstance(self::getConfigurationFile());
        $suites = $configuration->getTestSuiteConfiguration();
        $suite = new TestSuite();
        /** @var TestSuite $testSuite */
        foreach ($suites as $testSuite) {
            if ($testSuite->getName() === 'Magento Integration Tests') {
                continue;
            }
            /** @var TestSuite $test */
            foreach ($testSuite as $test) {
                $testName = $test->getName();

                if ($overrideConfig->hasConfiguration($testName) && !$test instanceof SkippableInterface) {
                    $reflectionClass = new \ReflectionClass($testName);
                    $resultTest = $generator->generateTestWrapper($reflectionClass);
                    $suite->addTest(new TestSuite($resultTest, $testName));
                } else {
                    $suite->addTest($test);
                }
            }
        }

        return $suite;
    }

    /**
     * Returns config file name from command line params.
     *
     * @return string
     */
    private static function getConfigurationFile(): string
    {
        $params = getopt('c:', ['configuration:']);
        $longConfig = $params['configuration'] ?? '';
        $shortConfig = $params['c'] ?? '';

        return $shortConfig ? $shortConfig : $longConfig;
    }
}

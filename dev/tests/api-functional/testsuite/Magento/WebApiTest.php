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
use PHPUnit\TextUI\Configuration\Configuration as LegacyConfiguration;
use PHPUnit\TextUI\Configuration\Registry;
use PHPUnit\TextUI\Configuration\TestSuite as LegacyTestSuiteConfiguration;
use PHPUnit\TextUI\Configuration\TestSuiteCollection as LegacyTestSuiteCollection;
use PHPUnit\TextUI\Configuration\TestSuiteMapper as LegacyTestSuiteMapper;
use PHPUnit\TextUI\XmlConfiguration\Configuration;
use PHPUnit\TextUI\XmlConfiguration\Loader;
use PHPUnit\TextUI\XmlConfiguration\TestSuite as TestSuiteConfiguration;
use PHPUnit\TextUI\XmlConfiguration\TestSuiteCollection;
use PHPUnit\TextUI\XmlConfiguration\TestSuiteMapper;

/**
 * Web API tests wrapper.
 */
class WebApiTest extends TestSuite
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param string $className
     * @return TestSuite
     * @throws \ReflectionException
     */
    public static function suite($className)
    {
        $generator = new WrapperGenerator();
        $overrideConfig = Config::getInstance();
        $configuration = self::getConfiguration();
        $suitesConfig = $configuration->testSuite();
        $suite = new TestSuite();
        foreach ($suitesConfig as $suiteConfig) {
            $suites = self::getSuites($suiteConfig);
            /** @var TestSuite $testSuite */
            foreach ($suites as $testSuite) {
                /** @var TestSuite $test */
                foreach ($testSuite as $test) {
                    $testName = $test->getName();

                    if ($overrideConfig->hasSkippedTest($testName) && !$test instanceof SkippableInterface) {
                        $reflectionClass = new \ReflectionClass($testName);
                        $resultTest = $generator->generateTestWrapper($reflectionClass);
                        $suite->addTest(new TestSuite($resultTest, $testName));
                    } else {
                        $suite->addTest($test);
                    }
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

    /**
     * Retrieve configuration depends on used phpunit version
     *
     * @return Configuration|LegacyConfiguration
     */
    private static function getConfiguration()
    {
        // Compatibility with phpunit < 9.3
        if (!class_exists(Configuration::class)) {
            // @phpstan-ignore-next-line
            return Registry::getInstance()->get(self::getConfigurationFile());
        }

        // @phpstan-ignore-next-line
        return (new Loader())->load(self::getConfigurationFile());
    }

    /**
     * Retrieve test suites by suite config depends on used phpunit version
     *
     * @param TestSuiteConfiguration|LegacyTestSuiteConfiguration $suiteConfig
     * @return TestSuite
     */
    private static function getSuites($suiteConfig)
    {
        // Compatibility with phpunit < 9.3
        if (!class_exists(Configuration::class)) {
            // @phpstan-ignore-next-line
            return (new LegacyTestSuiteMapper())->map(LegacyTestSuiteCollection::fromArray([$suiteConfig]), '');
        }

        // @phpstan-ignore-next-line
        return (new TestSuiteMapper())->map(TestSuiteCollection::fromArray([$suiteConfig]), '');
    }
}

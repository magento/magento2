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
use PHPUnit\TextUI\Configuration\Registry;
use PHPUnit\TextUI\Configuration\TestSuiteCollection;
use PHPUnit\TextUI\Configuration\TestSuiteMapper;

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
        $configuration = Registry::getInstance()->get(self::getConfigurationFile());
        $suitesConfig = $configuration->testSuite();
        $suite = new TestSuite();
        /** @var \PHPUnit\TextUI\Configuration\TestSuite $suiteConfig */
        foreach ($suitesConfig as $suiteConfig) {
            if ($suiteConfig->name() === 'Magento Integration Tests') {
                continue;
            }
            $suites = (new TestSuiteMapper())->map(TestSuiteCollection::fromArray([$suiteConfig]), '');
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
}

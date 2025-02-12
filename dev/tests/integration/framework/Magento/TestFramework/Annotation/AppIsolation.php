<?php
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */
namespace Magento\TestFramework\Annotation;

use Magento\Framework\Exception\LocalizedException;
use Magento\TestFramework\Application;
use Magento\TestFramework\Fixture\ParserInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\AbstractController;
use PHPUnit\Framework\TestCase;

/**
 * Implementation of the @magentoAppIsolation DocBlock annotation - isolation of global application objects in memory
 */
class AppIsolation
{
    /**
     * Flag to prevent an excessive test case isolation if the last test has been just isolated
     *
     * @var bool
     */
    private $hasNonIsolatedTests = true;

    /**
     * @var Application
     */
    private $application;

    /**
     * @var array
     */
    private $serverGlobalBackup;

    /**
     * Constructor
     *
     * @param Application $application
     */
    public function __construct(Application $application)
    {
        $this->application = $application;
    }

    /**
     * Isolate global application objects
     */
    protected function _isolateApp()
    {
        if ($this->hasNonIsolatedTests) {
            $this->application->reinitialize();
            $_SESSION = [];
            $_COOKIE = [];
            session_write_close();
            $this->hasNonIsolatedTests = false;
        }
    }

    /**
     * Isolate application before running test case
     */
    public function startTestSuite()
    {
        $this->serverGlobalBackup = $_SERVER;
        $this->_isolateApp();
    }

    /**
     * Isolate application after running test case
     */
    public function endTestSuite()
    {
        $_SERVER = $this->serverGlobalBackup;
    }

    /**
     * Handler for 'endTest' event
     *
     * @param TestCase $test
     * @throws LocalizedException
     */
    public function endTest(TestCase $test)
    {
        $this->hasNonIsolatedTests = true;
        $values = [];
        try {
            $values = $this->parse($test);
        } catch (\Throwable $exception) {
            ExceptionHandler::handle(
                'Unable to parse annotations',
                $exception,
                $test
            );
        }
        if ($values) {
            $isIsolationEnabled = $values[0]['enabled'];
        } else {
            /* Controller tests should be isolated by default */
            $isIsolationEnabled = $test instanceof AbstractController;
        }

        if ($isIsolationEnabled) {
            $this->_isolateApp();
        }
    }

    /**
     * Returns AppIsolation fixtures configuration
     *
     * @param TestCase $test
     * @return array
     * @throws LocalizedException
     */
    private function parse(TestCase $test): array
    {
        $objectManager = Bootstrap::getObjectManager();
        $parsers = $objectManager
            ->create(
                \Magento\TestFramework\Annotation\Parser\Composite::class,
                [
                    'parsers' => [
                        $objectManager->get(\Magento\TestFramework\Annotation\Parser\AppIsolation::class),
                        $objectManager->get(\Magento\TestFramework\Fixture\Parser\AppIsolation::class)
                    ]
                ]
            );
        $values = $parsers->parse($test, ParserInterface::SCOPE_METHOD)
            ?: $parsers->parse($test, ParserInterface::SCOPE_CLASS);

        if (count($values) > 1) {
            throw new LocalizedException(
                __('Only one "@magentoAppIsolation" annotation is allowed per test')
            );
        }
        return $values;
    }
}

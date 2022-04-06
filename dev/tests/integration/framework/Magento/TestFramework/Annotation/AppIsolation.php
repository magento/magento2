<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Implementation of the @magentoAppIsolation DocBlock annotation - isolation of global application objects in memory
 */
namespace Magento\TestFramework\Annotation;

use Magento\Framework\Exception\LocalizedException;
use Magento\TestFramework\Annotation\TestCaseAnnotation;
use Magento\TestFramework\Application;
use Magento\TestFramework\TestCase\AbstractController;
use PHPUnit\Framework\TestCase;

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

        /* Determine an isolation from doc comment */
        $annotations = $this->getAnnotations($test);
        if (isset($annotations['magentoAppIsolation'])) {
            $isolation = $annotations['magentoAppIsolation'];
            if ($isolation !== ['enabled'] && $isolation !== ['disabled']) {
                throw new LocalizedException(
                    __('Invalid "@magentoAppIsolation" annotation, can be "enabled" or "disabled" only.')
                );
            }
            $isIsolationEnabled = $isolation === ['enabled'];
        } else {
            /* Controller tests should be isolated by default */
            $isIsolationEnabled = $test instanceof AbstractController;
        }

        if ($isIsolationEnabled) {
            $this->_isolateApp();
        }
    }

    /**
     * Get method annotations. Overwrites class-defined annotations.
     *
     * @param TestCase $test
     *
     * @return array
     */
    private function getAnnotations(TestCase $test): array
    {
        $annotations = TestCaseAnnotation::getInstance()->getAnnotations($test);

        return array_replace((array)$annotations['class'], (array)$annotations['method']);
    }
}

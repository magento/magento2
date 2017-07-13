<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Implementation of the @magentoAppIsolation DocBlock annotation - isolation of global application objects in memory
 */
namespace Magento\TestFramework\Annotation;

class AppIsolation
{
    /**
     * Flag to prevent an excessive test case isolation if the last test has been just isolated
     *
     * @var bool
     */
    private $_hasNonIsolatedTests = true;

    /**
     * @var \Magento\TestFramework\Application
     */
    private $_application;

    /**
     * Constructor
     *
     * @param \Magento\TestFramework\Application $application
     */
    public function __construct(\Magento\TestFramework\Application $application)
    {
        $this->_application = $application;
    }

    /**
     * Isolate global application objects
     */
    protected function _isolateApp()
    {
        if ($this->_hasNonIsolatedTests) {
            $this->_application->reinitialize();
            $_SESSION = [];
            $_COOKIE = [];
            session_write_close();
            $this->_hasNonIsolatedTests = false;
        }
    }

    /**
     * Isolate application before running test case
     */
    public function startTestSuite()
    {
        $this->_isolateApp();
    }

    /**
     * Handler for 'endTest' event
     *
     * @param \PHPUnit\Framework\TestCase $test
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function endTest(\PHPUnit\Framework\TestCase $test)
    {
        $this->_hasNonIsolatedTests = true;

        /* Determine an isolation from doc comment */
        $annotations = $test->getAnnotations();
        $annotations = array_replace((array) $annotations['class'], (array) $annotations['method']);
        if (isset($annotations['magentoAppIsolation'])) {
            $isolation = $annotations['magentoAppIsolation'];
            if ($isolation !== ['enabled'] && $isolation !== ['disabled']) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Invalid "@magentoAppIsolation" annotation, can be "enabled" or "disabled" only.')
                );
            }
            $isIsolationEnabled = $isolation === ['enabled'];
        } else {
            /* Controller tests should be isolated by default */
            $isIsolationEnabled = $test instanceof \Magento\TestFramework\TestCase\AbstractController;
        }

        if ($isIsolationEnabled) {
            $this->_isolateApp();
        }
    }
}

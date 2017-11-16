<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\TestFramework\Annotation;

/**
 * Handler for applying reinstallMagento annotation
 *
 */
class ReinstallInstance
{
    /**
     * @var \Magento\TestFramework\Application
     */
    private $application;

    /**
     * Constructor
     *
     * @param \Magento\TestFramework\Application $application
     */
    public function __construct(\Magento\TestFramework\Application $application)
    {
        $this->application = $application;
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
        $isIsolationEnabled = false;
        if (isset($annotations['reinstallMagento'])) {
            $isolation = $annotations['reinstallMagento'];
            if ($isolation !== ['enabled'] && $isolation !== ['disabled']) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Invalid "@reinstallMagento" annotation, can be "enabled" or "disabled" only.')
                );
            }
            $isIsolationEnabled = $isolation === ['enabled'];
        }
        if ($isIsolationEnabled && $this->application->isInstalled()) {
            $this->application->cleanup();
            $this->application->install();
        }
    }
}

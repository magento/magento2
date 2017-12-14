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
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function endTest()
    {
        if ($this->application->isInstalled()) {
            $this->application->cleanup();
        }
    }
}

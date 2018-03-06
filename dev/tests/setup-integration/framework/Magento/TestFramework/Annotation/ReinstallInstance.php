<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\TestFramework\Annotation;

use Magento\Framework\Module\ModuleResource;

/**
 * Handler for applying reinstallMagento annotation.
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

    public function startTest()
    {
        $this->application->reinitialize();
    }

    /**
     * Handler for 'endTest' event.
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function endTest()
    {
        $this->application->cleanup();
        $this->application->reinitialize();
        ModuleResource::flush();
    }
}

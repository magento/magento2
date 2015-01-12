<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Bootstrap for the integration testing environment
 */
namespace Magento\TestFramework;

class Bootstrap
{
    /**#@+
     * Predefined admin user credentials
     */
    const ADMIN_NAME = 'user';
    const ADMIN_PASSWORD = 'password1';
    const ADMIN_EMAIL = 'admin@example.com';
    const ADMIN_FIRSTNAME = 'firstname';
    const ADMIN_LASTNAME = 'lastname';
    /**#@- */

    /**
     * Predefined admin user role name
     */
    const ADMIN_ROLE_NAME = 'Administrators';

    /**
     * @var \Magento\TestFramework\Bootstrap\Settings
     */
    private $_settings;

    /**
     * @var \Magento\TestFramework\Application
     */
    private $_application;

    /**
     * @var \Magento\TestFramework\Bootstrap\Environment
     */
    private $_envBootstrap;

    /**
     * @var \Magento\TestFramework\Bootstrap\DocBlock
     */
    private $_docBlockBootstrap;

    /**
     * @var \Magento\TestFramework\Bootstrap\Profiler
     */
    private $_profilerBootstrap;

    /**
     * @var \Magento\Framework\Shell
     */
    private $_shell;

    /**
     * @var \Magento\TestFramework\Bootstrap\MemoryFactory
     */
    private $memoryFactory;

    /**
     * Constructor
     *
     * @param \Magento\TestFramework\Bootstrap\Settings $settings
     * @param \Magento\TestFramework\Bootstrap\Environment $envBootstrap
     * @param \Magento\TestFramework\Bootstrap\DocBlock $docBlockBootstrap
     * @param \Magento\TestFramework\Bootstrap\Profiler $profilerBootstrap
     * @param \Magento\Framework\Shell $shell
     * @param Application $application
     * @param Bootstrap\MemoryFactory $memoryFactory
     * @internal param string $tmpDir
     */
    public function __construct(
        \Magento\TestFramework\Bootstrap\Settings $settings,
        \Magento\TestFramework\Bootstrap\Environment $envBootstrap,
        \Magento\TestFramework\Bootstrap\DocBlock $docBlockBootstrap,
        \Magento\TestFramework\Bootstrap\Profiler $profilerBootstrap,
        \Magento\Framework\Shell $shell,
        \Magento\TestFramework\Application $application,
        \Magento\TestFramework\Bootstrap\MemoryFactory $memoryFactory
    ) {
        $this->_settings = $settings;
        $this->_envBootstrap = $envBootstrap;
        $this->_docBlockBootstrap = $docBlockBootstrap;
        $this->_profilerBootstrap = $profilerBootstrap;
        $this->_shell = $shell;
        $this->_application = $application;
        $this->memoryFactory = $memoryFactory;
    }

    /**
     * Retrieve the application instance
     *
     * @return Application
     */
    public function getApplication()
    {
        return $this->_application;
    }

    /**
     * Perform bootstrap actions required to completely setup the testing environment
     */
    public function runBootstrap()
    {
        $this->_envBootstrap->emulateHttpRequest($_SERVER);
        $this->_envBootstrap->emulateSession($_SESSION);

        $profilerOutputFile = $this->_settings->getAsFile('TESTS_PROFILER_FILE');
        if ($profilerOutputFile) {
            $this->_profilerBootstrap->registerFileProfiler($profilerOutputFile);
        }

        $profilerBambooOutputFile = $this->_settings->getAsFile('TESTS_BAMBOO_PROFILER_FILE');
        $profilerBambooMetricsFile = $this->_settings->getAsFile('TESTS_BAMBOO_PROFILER_METRICS_FILE');
        if ($profilerBambooOutputFile && $profilerBambooMetricsFile) {
            $this->_profilerBootstrap->registerBambooProfiler($profilerBambooOutputFile, $profilerBambooMetricsFile);
        }

        $memoryBootstrap = $this->memoryFactory->create(
            $this->_settings->get('TESTS_MEM_USAGE_LIMIT', 0),
            $this->_settings->get('TESTS_MEM_LEAK_LIMIT', 0)
        );
        $memoryBootstrap->activateStatsDisplaying();
        $memoryBootstrap->activateLimitValidation();

        $this->_docBlockBootstrap->registerAnnotations($this->_application);
    }
}

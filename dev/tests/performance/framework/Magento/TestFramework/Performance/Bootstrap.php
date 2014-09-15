<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Bootstrap for performance tests
 */
namespace Magento\TestFramework\Performance;

class Bootstrap
{
    /**
     * The real application bootstrap
     *
     * @var \Magento\Framework\App\Bootstrap
     */
    private $appBootstrap;

    /**
     * Tests base directory
     *
     * @var string
     */
    private $testsBaseDir;

    /**
     * Tests configuration holder
     *
     * @var \Magento\TestFramework\Performance\Config
     */
    private $config;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Bootstrap $appBootstrap
     * @param string $testsBaseDir
     */
    public function __construct(\Magento\Framework\App\Bootstrap $appBootstrap, $testsBaseDir)
    {
        $this->appBootstrap = $appBootstrap;
        $this->testsBaseDir = $testsBaseDir;
    }

    /**
     * Ensure reports directory exists, empty, and has write permissions
     *
     * @throws \Magento\Framework\Exception
     */
    public function cleanupReports()
    {
        $reportDir = $this->getConfig()->getReportDir();
        try {
            $filesystemAdapter = new \Magento\Framework\Filesystem\Driver\File();
            if ($filesystemAdapter->isExists($reportDir)) {
                $filesystemAdapter->deleteDirectory($reportDir);
            }
        } catch (\Magento\Framework\Filesystem\FilesystemException $e) {
            if (file_exists($reportDir)) {
                throw new \Magento\Framework\Exception("Cannot cleanup reports directory '{$reportDir}'.");
            }
        }
        mkdir($reportDir, 0777, true);
    }

    /**
     * Test framework application factory method
     *
     * @param \Magento\Framework\Shell $shell
     * @return \Magento\TestFramework\Application
     */
    public function createApplication(\Magento\Framework\Shell $shell)
    {
        return new \Magento\TestFramework\Application(
            $this->getConfig(),
            $this->appBootstrap->getObjectManager(),
            $shell
        );
    }

    /**
     * Test suite factory method
     *
     * @param \Magento\TestFramework\Application $application
     * @param \Magento\TestFramework\Performance\Scenario\HandlerInterface $scenarioHandler
     * @return Testsuite
     */
    public function createTestSuite(
        \Magento\TestFramework\Application $application,
        \Magento\TestFramework\Performance\Scenario\HandlerInterface $scenarioHandler
    ) {
        return new Testsuite($this->getConfig(), $application, $scenarioHandler);
    }

    /**
     * Return configuration for the tests
     *
     * @return \Magento\TestFramework\Performance\Config
     */
    public function getConfig()
    {
        if (null === $this->config) {
            $configFile = "{$this->testsBaseDir}/config.php";
            $configFile = file_exists($configFile) ? $configFile : "{$configFile}.dist";
            $configData = require $configFile;
            $this->config = new Config($configData, $this->testsBaseDir, $this->appBootstrap->getDirList()->getRoot());
        }
        return $this->config;
    }
}

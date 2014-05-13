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
     * Tests configuration holder
     *
     * @var \Magento\TestFramework\Performance\Config
     */
    protected $_config;

    /**
     * Constructor
     *
     * @param string $testsBaseDir
     * @param string $appBaseDir
     */
    public function __construct($testsBaseDir, $appBaseDir)
    {
        $configFile = "{$testsBaseDir}/config.php";
        $configFile = file_exists($configFile) ? $configFile : "{$configFile}.dist";
        $configData = require $configFile;
        $this->_config = new \Magento\TestFramework\Performance\Config($configData, $testsBaseDir, $appBaseDir);
    }

    /**
     * Ensure reports directory exists, empty, and has write permissions
     *
     * @throws \Magento\Framework\Exception
     */
    public function cleanupReports()
    {
        $reportDir = $this->_config->getReportDir();
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
     * Return configuration for the tests
     *
     * @return \Magento\TestFramework\Performance\Config
     */
    public function getConfig()
    {
        return $this->_config;
    }
}

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
 * @category   Tools
 * @package    view
 * @copyright  Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Parses, verifies and stores command-line parameters
 */
namespace Magento\Tools\View\Generator;

class Config
{
    /**
     * @var string
     */
    private $_sourceDir;

    /**
     * @var string
     */
    private $_destinationDir;

    /**
     * @var bool
     */
    private $_isDryRun;

    /**
     * @param string $appBaseDir
     * @param array $cmdOptions
     * @throws \Magento\Exception
     */
    public function __construct($appBaseDir, $cmdOptions)
    {
        $sourceDir = isset($cmdOptions['source']) ? $cmdOptions['source'] : $appBaseDir;
        if (!is_dir($sourceDir)) {
            throw new \Magento\Exception('Source directory does not exist: ' . $sourceDir);
        }

        if (isset($cmdOptions['destination'])) {
            $destinationDir = $cmdOptions['destination'];
        } else {
            $dirs = new \Magento\App\Dir($sourceDir);
            $destinationDir = $dirs->getDir(\Magento\App\Dir::STATIC_VIEW);
        }
        if (!is_dir($destinationDir)) {
            throw new \Magento\Exception('Destination directory does not exist: ' . $destinationDir);
        }
        if (glob($destinationDir . DIRECTORY_SEPARATOR . '*')) {
            throw new \Magento\Exception("Destination directory must be empty: {$destinationDir}");
        }

        $isDryRun = isset($cmdOptions['dry-run']);

        // Assign to internal values
        $this->_sourceDir = $sourceDir;
        $this->_destinationDir = $destinationDir;
        $this->_isDryRun = $isDryRun;
    }

    /**
     * Return configured source path
     *
     * @return string
     */
    public function getSourceDir()
    {
        return $this->_sourceDir;
    }

    /**
     * Return configured destination path
     *
     * @return string
     */
    public function getDestinationDir()
    {
        return $this->_destinationDir;
    }

    /**
     * Return, whether dry run is turned on
     *
     * @return bool
     */
    public function isDryRun()
    {
        return $this->_isDryRun;
    }
}

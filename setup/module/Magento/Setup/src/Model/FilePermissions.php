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
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Setup\Model;

use Magento\Config\Config;
use Magento\Config\ConfigFactory;
use Magento\Filesystem\Filesystem;

class FilePermissions
{
    /**
     * @var ConfigFactory
     */
    protected $configFactory;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * List of required directories
     *
     * @var array
     */
    protected $required = [];

    /**
     * List of currently existed directories
     *
     * @var array
     */
    protected $current = [];

    /**
     * @param Filesystem $filesystem
     * @param ConfigFactory $configFactory
     */
    public function __construct(
        Filesystem $filesystem,
        ConfigFactory $configFactory
    ) {
        $this->filesystem = $filesystem;

        $this->configFactory = $configFactory;
        $this->config = $this->configFactory->create();
    }

    /**
     * Retrieve list of required directories
     *
     * @return array
     */
    public function getRequired()
    {
        if (!$this->required) {
            foreach ($this->config->getMagentoFilePermissions() as $code => $config) {
                if (isset($config['path'])) {
                    $this->required[$code] = $config['path'];
                }
            }
        }
        return array_values($this->required);
    }

    /**
     * Retrieve list of currently existed directories
     *
     * @return array
     */
    public function getCurrent()
    {
        if (!$this->current) {
            foreach ($this->required as $code => $path) {
                if (!$this->validate($code)) {
                    continue;
                }
                $this->current[$code] = $path;
            }
        }
        return array_values($this->current);
    }

    /**
     * Validate directory permissions by given directory code
     *
     * @param string $code
     * @return bool
     */
    protected function validate($code)
    {
        $directory = $this->filesystem->getDirectoryWrite($code);
        if (!$directory->isExist()) {
            return false;
        }
        if (!$directory->isDirectory()) {
            return false;
        }
        if (!$directory->isReadable()) {
            return false;
        }
        if (!$directory->isWritable()) {
            return false;
        }
        return true;
    }

    /**
     * Checks if has file permission or not
     *
     * @return array
     */
    public function checkPermission()
    {
        $required = $this->getRequired();
        $current = $this->getCurrent();
        return array_diff($required, $current);
    }
}

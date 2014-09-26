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

namespace Magento\Setup\Module;

use Magento\Config\Config;
use Zend\Stdlib\Glob;
use Magento\Config\FileResolverInterface;
use Magento\Config\FileIteratorFactory;
use Magento\Config\ConfigFactory;

class FileResolver implements FileResolverInterface
{
    /**
     * @var FileIteratorFactory
     */
    protected $iteratorFactory;

    /**
     * @var ConfigFactory
     */
    protected $configFactory;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @param FileIteratorFactory $iteratorFactory
     * @param ConfigFactory $configFactory
     * @internal param Config $config
     */
    public function __construct(
        FileIteratorFactory $iteratorFactory,
        ConfigFactory $configFactory
    ) {
        $this->iteratorFactory = $iteratorFactory;
        $this->configFactory = $configFactory;
        $this->config = $this->configFactory->create();
    }

    /**
     * @param string $filename
     * @return array
     */
    public function get($filename)
    {
        $paths = [];

        // Collect files by /app/code/*/*/etc/{filename} pattern
        $files = $this->getFiles($this->config->getMagentoModulePath() . '*/*/etc/' . $filename);
        foreach ($files as $file) {
            $paths[] = $this->getRelativePath($file);
        }

        // Collect files by /app/etc/*/{filename} pattern
        $files = $this->getFiles($this->config->getMagentoConfigPath() . '*/' . $filename);
        foreach ($files as $file) {
            $paths[] = $this->getRelativePath($file);
        }

        return $this->iteratorFactory->create($this->config->getMagentoBasePath(), $paths);
    }

    /**
     * Retrieves relative path
     *
     * @param string $path
     * @return string
     */
    protected function getRelativePath($path = null)
    {
        $basePath = $this->config->getMagentoBasePath();
        if (strpos($path, $basePath) === 0
            || $basePath == $path . '/') {
            $result = substr($path, strlen($basePath));
        } else {
            $result = $path;
        }
        return $result;
    }

    /**
     * @param string $path
     * @return array|false
     */
    protected function getFiles($path)
    {
        return Glob::glob($this->config->getMagentoBasePath() . $path);
    }
}

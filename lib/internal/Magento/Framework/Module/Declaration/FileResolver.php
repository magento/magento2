<?php
/**
 * Module declaration file resolver. Reads list of module declaration files from module /etc directories.
 *
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
namespace Magento\Framework\Module\Declaration;

use Magento\Framework\App\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;

class FileResolver implements \Magento\Framework\Config\FileResolverInterface
{
    /**
     * Modules directory with read access
     *
     * @var ReadInterface
     */
    protected $modulesDirectory;

    /**
     * Config directory with read access
     *
     * @var ReadInterface
     */
    protected $configDirectory;

    /**
     * Root directory with read access
     *
     * @var ReadInterface
     */
    protected $rootDirectory;

    /**
     * File iterator factory
     *
     * @var \Magento\Framework\Config\FileIteratorFactory
     */
    protected $iteratorFactory;

    /**
     * @param Filesystem $filesystem
     * @param \Magento\Framework\Config\FileIteratorFactory $iteratorFactory
     */
    public function __construct(Filesystem $filesystem, \Magento\Framework\Config\FileIteratorFactory $iteratorFactory)
    {
        $this->iteratorFactory = $iteratorFactory;
        $this->modulesDirectory = $filesystem->getDirectoryRead(Filesystem::MODULES_DIR);
        $this->configDirectory = $filesystem->getDirectoryRead(Filesystem::CONFIG_DIR);
        $this->rootDirectory = $filesystem->getDirectoryRead(Filesystem::ROOT_DIR);
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function get($filename, $scope)
    {
        $moduleDir = $this->modulesDirectory->getAbsolutePath();
        $configDir = $this->configDirectory->getAbsolutePath();

        $mageScopePath = $moduleDir . '/Magento';
        $output = array('base' => array(), 'mage' => array(), 'custom' => array());
        $files = glob($moduleDir . '*/*/etc/module.xml');
        if (!empty($files)) {
            foreach ($files as $file) {
                $scope = strpos($file, $mageScopePath) === 0 ? 'mage' : 'custom';
                $output[$scope][] = $this->rootDirectory->getRelativePath($file);
            }
        }
        $files = glob($configDir . '*/module.xml');
        if (!empty($files)) {
            foreach ($files as $file) {
                $output['base'][] = $this->rootDirectory->getRelativePath($file);
            }
        }
        return $this->iteratorFactory->create(
            $this->rootDirectory,
            array_merge($output['mage'], $output['custom'], $output['base'])
        );
    }
}

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
 * @copyright Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Module\Declaration;

class FileResolver implements \Magento\Config\FileResolverInterface
{
    /**
     * Modules directory with read access
     *
     * @var \Magento\Filesystem\Directory\ReadInterface
     */
    protected $directoryReadModule;

    /**
     * Config directory with read access
     *
     * @var \Magento\Filesystem\Directory\ReadInterface
     */
    protected $directoryReadConfig;

    /**
     * Root directory with read access
     *
     * @var \Magento\Filesystem\Directory\ReadInterface
     */
    protected $directoryReadRoot;

    /**
     * File iterator factory
     *
     * @var FileIteratorFactory
     */
    protected $iteratorFactory;

    /**
     * @param \Magento\Filesystem $filesystem
     * @param \Magento\Config\FileIteratorFactory $iteratorFactory
     */
    public function __construct(
        \Magento\Filesystem $filesystem,
        \Magento\Config\FileIteratorFactory $iteratorFactory
    ) {
        $this->iteratorFactory      = $iteratorFactory;
        $this->directoryReadModules = $filesystem->getDirectoryRead(\Magento\Filesystem::MODULES);
        $this->directoryReadConfig  = $filesystem->getDirectoryRead(\Magento\Filesystem::CONFIG);
        $this->directoryReadRoot     = $filesystem->getDirectoryRead(\Magento\Filesystem::ROOT);
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function get($filename, $scope)
    {
        $appCodeDir =  $this->directoryReadRoot->getRelativePath(
            $this->directoryReadModules->getAbsolutePath()
        );
        $configDir =  $this->directoryReadRoot->getRelativePath(
            $this->directoryReadConfig->getAbsolutePath()
        );
        $moduleFileList = $this->directoryReadRoot->search('#.*?/module.xml$#', $appCodeDir);

        $mageScopePath = $appCodeDir . '/Magento/';
        $output = array(
            'base' => array(),
            'mage' => array(),
            'custom' => array(),
        );
        foreach ($moduleFileList as $file) {
            $scope = strpos($file, $mageScopePath) === 0 ? 'mage' : 'custom';
            $output[$scope][] = $file;
        }
        $output['base'] = $this->directoryReadRoot->search('#/module.xml$#', $configDir);

        return $this->iteratorFactory->create(
            $this->directoryReadRoot,
            array_merge($output['mage'], $output['custom'], $output['base'])
        );
    }
}

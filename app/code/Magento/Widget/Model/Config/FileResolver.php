<?php
/**
 * Application config file resolver
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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Widget\Model\Config;

class FileResolver implements \Magento\Framework\Config\FileResolverInterface
{
    /**
     * Module configuration file reader
     *
     * @var \Magento\Framework\Module\Dir\Reader
     */
    protected $_moduleReader;

    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadInterface
     */
    protected $themesDirectory;

    /**
     * @var \Magento\Framework\Config\FileIteratorFactory
     */
    protected $iteratorFactory;

    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadInterface
     */
    protected $modulesDirectory;

    /**
     * @param \Magento\Framework\App\Filesystem                   $filesystem
     * @param \Magento\Framework\Module\Dir\Reader            $moduleReader
     * @param \Magento\Framework\Config\FileIteratorFactory   $iteratorFactory
     */
    public function __construct(
        \Magento\Framework\App\Filesystem $filesystem,
        \Magento\Framework\Module\Dir\Reader $moduleReader,
        \Magento\Framework\Config\FileIteratorFactory $iteratorFactory
    ) {
        $this->themesDirectory = $filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem::THEMES_DIR);
        $this->modulesDirectory = $filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem::MODULES_DIR);
        $this->iteratorFactory = $iteratorFactory;
        $this->_moduleReader = $moduleReader;
    }

    /**
     * {@inheritdoc}
     */
    public function get($filename, $scope)
    {
        switch ($scope) {
            case 'global':
                $iterator = $this->_moduleReader->getConfigurationFiles($filename);
                break;
            case 'design':
                $iterator = $this->iteratorFactory->create(
                    $this->themesDirectory,
                    $this->themesDirectory->search('/*/*/etc/' . $filename)
                );
                break;
            default:
                $iterator = $this->iteratorFactory->create($this->themesDirectory, array());
                break;
        }
        return $iterator;
    }
}

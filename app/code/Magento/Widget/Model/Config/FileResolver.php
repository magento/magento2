<?php
/**
 * Application config file resolver
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Widget\Model\Config;

use Magento\Framework\App\Filesystem\DirectoryList;

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
     * @param \Magento\Framework\Filesystem                   $filesystem
     * @param \Magento\Framework\Module\Dir\Reader            $moduleReader
     * @param \Magento\Framework\Config\FileIteratorFactory   $iteratorFactory
     */
    public function __construct(
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Module\Dir\Reader $moduleReader,
        \Magento\Framework\Config\FileIteratorFactory $iteratorFactory
    ) {
        $this->themesDirectory = $filesystem->getDirectoryRead(DirectoryList::THEMES);
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
                $iterator = $this->iteratorFactory->create($this->themesDirectory, []);
                break;
        }
        return $iterator;
    }
}

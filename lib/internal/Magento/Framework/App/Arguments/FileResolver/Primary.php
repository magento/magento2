<?php
/**
 * Application primary config file resolver
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Arguments\FileResolver;

use Magento\Framework\App\Filesystem\DirectoryList;

class Primary implements \Magento\Framework\Config\FileResolverInterface
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
    protected $configDirectory;

    /**
     * @var \Magento\Framework\Config\FileIteratorFactory
     */
    protected $iteratorFactory;

    /**
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\Config\FileIteratorFactory $iteratorFactory
     */
    public function __construct(
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Config\FileIteratorFactory $iteratorFactory
    ) {
        $this->configDirectory = $filesystem->getDirectoryRead(DirectoryList::CONFIG);
        $this->iteratorFactory = $iteratorFactory;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function get($filename, $scope)
    {
        return $this->iteratorFactory->create(
            $this->configDirectory,
            $this->configDirectory->search('{*' . $filename . ',*/*' . $filename . '}')
        );
    }
}

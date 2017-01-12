<?php
/**
 * Application primary config file resolver
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Arguments\FileResolver;

use Magento\Framework\App\Filesystem\DirectoryList;

class Primary implements \Magento\Framework\Config\FileResolverInterface
{
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
        $configPaths = $this->configDirectory->search('{*' . $filename . ',*/*' . $filename . '}');
        $configAbsolutePaths = [];
        foreach ($configPaths as $configPath) {
            $configAbsolutePaths[] = $this->configDirectory->getAbsolutePath($configPath);
        }
        return $this->iteratorFactory->create($configAbsolutePaths);
    }
}

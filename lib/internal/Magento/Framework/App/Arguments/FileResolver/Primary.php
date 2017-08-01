<?php
/**
 * Application primary config file resolver
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Arguments\FileResolver;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Class \Magento\Framework\App\Arguments\FileResolver\Primary
 *
 * @since 2.0.0
 */
class Primary implements \Magento\Framework\Config\FileResolverInterface
{
    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadInterface
     * @since 2.0.0
     */
    protected $configDirectory;

    /**
     * @var \Magento\Framework\Config\FileIteratorFactory
     * @since 2.0.0
     */
    protected $iteratorFactory;

    /**
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\Config\FileIteratorFactory $iteratorFactory
     * @since 2.0.0
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
     * @since 2.0.0
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

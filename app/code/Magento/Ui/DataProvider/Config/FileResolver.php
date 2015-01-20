<?php
/**
 * Hierarchy config file resolver
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\DataProvider\Config;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Config\FileIteratorFactory;
use Magento\Framework\Config\FileResolverInterface;
use Magento\Framework\Filesystem;

/**
 * Class FileResolver
 */
class FileResolver implements \Magento\Framework\Config\FileResolverInterface
{
    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadInterface
     */
    protected $directoryRead;

    /**
     * @var FileIteratorFactory
     */
    protected $iteratorFactory;

    /**
     * @param Filesystem $filesystem
     * @param FileIteratorFactory $iteratorFactory
     */
    public function __construct(
        Filesystem $filesystem,
        FileIteratorFactory $iteratorFactory
    ) {
        $this->directoryRead = $filesystem->getDirectoryRead(DirectoryList::MODULES);
        $this->iteratorFactory = $iteratorFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function get($filename, $scope)
    {
        $iterator = $this->iteratorFactory->create(
            $this->directoryRead,
            $this->directoryRead->search('/*/*/etc/data_source/' . $filename)
        );
        return $iterator;
    }
}

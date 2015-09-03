<?php
/**
 * Hierarchy config file resolver
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Email\Model\Template\Config;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Config\FileIteratorFactory;
use Magento\Framework\Module\Dir\Search;

class FileResolver implements \Magento\Framework\Config\FileResolverInterface
{
    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadInterface
     */
    protected $directoryRead;

    /**
     * @var \Magento\Framework\Config\FileIteratorFactory
     */
    protected $iteratorFactory;

    /**
     * @var Search
     */
    protected $dirSearch;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Filesystem $filesystem
     * @param FileIteratorFactory $iteratorFactory
     * @param Search $dirSearch
     */
    public function __construct(
        \Magento\Framework\Filesystem $filesystem,
        FileIteratorFactory $iteratorFactory,
        Search $dirSearch
    ) {
        $this->directoryRead = $filesystem->getDirectoryRead(DirectoryList::ROOT);
        $this->iteratorFactory = $iteratorFactory;
        $this->dirSearch = $dirSearch;
    }

    /**
     * {@inheritdoc}
     */
    public function get($filename, $scope)
    {
        $iterator = $this->iteratorFactory->create(
            $this->directoryRead,
            $this->dirSearch->collectFiles('etc/' . $filename)
        );
        return $iterator;
    }
}

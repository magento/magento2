<?php
/**
 * Hierarchy config file resolver
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Email\Model\Template\Config;

use Magento\Framework\Config\FileIteratorFactory;
use Magento\Framework\Module\Dir\Search;

class FileResolver implements \Magento\Framework\Config\FileResolverInterface
{
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
     * @param FileIteratorFactory $iteratorFactory
     * @param Search $dirSearch
     */
    public function __construct(
        FileIteratorFactory $iteratorFactory,
        Search $dirSearch
    ) {
        $this->iteratorFactory = $iteratorFactory;
        $this->dirSearch = $dirSearch;
    }

    /**
     * {@inheritdoc}
     */
    public function get($filename, $scope)
    {
        $iterator = $this->iteratorFactory->create($this->dirSearch->collectFiles('etc/' . $filename));
        return $iterator;
    }
}

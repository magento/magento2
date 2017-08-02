<?php
/**
 * Hierarchy config file resolver
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Email\Model\Template\Config;

use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Component\DirSearch;
use Magento\Framework\Config\FileIteratorFactory;

/**
 * Class \Magento\Email\Model\Template\Config\FileResolver
 *
 * @since 2.0.0
 */
class FileResolver implements \Magento\Framework\Config\FileResolverInterface
{
    /**
     * @var \Magento\Framework\Config\FileIteratorFactory
     * @since 2.0.0
     */
    protected $iteratorFactory;

    /**
     * @var DirSearch
     * @since 2.0.0
     */
    protected $dirSearch;

    /**
     * Constructor
     *
     * @param FileIteratorFactory $iteratorFactory
     * @param DirSearch $dirSearch
     * @since 2.0.0
     */
    public function __construct(
        FileIteratorFactory $iteratorFactory,
        DirSearch $dirSearch
    ) {
        $this->iteratorFactory = $iteratorFactory;
        $this->dirSearch = $dirSearch;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function get($filename, $scope)
    {
        $iterator = $this->iteratorFactory->create(
            $this->dirSearch->collectFiles(ComponentRegistrar::MODULE, 'etc/' . $filename)
        );
        return $iterator;
    }
}

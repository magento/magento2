<?php
/**
 * Application config file resolver
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Widget\Model\Config;

use Magento\Framework\Component\DirSearch;
use Magento\Framework\Component\ComponentRegistrar;

class FileResolver implements \Magento\Framework\Config\FileResolverInterface
{
    /**
     * Module configuration file reader
     *
     * @var \Magento\Framework\Module\Dir\Reader
     */
    protected $_moduleReader;

    /**
     * @var \Magento\Framework\Config\FileIteratorFactory
     */
    protected $iteratorFactory;

    /**
     * @var DirSearch
     */
    private $componentDirSearch;

    /**
     * @param \Magento\Framework\Module\Dir\Reader $moduleReader
     * @param \Magento\Framework\Config\FileIteratorFactory $iteratorFactory
     * @param DirSearch $componentDirSearch
     */
    public function __construct(
        \Magento\Framework\Module\Dir\Reader $moduleReader,
        \Magento\Framework\Config\FileIteratorFactory $iteratorFactory,
        DirSearch $componentDirSearch
    ) {
        $this->iteratorFactory = $iteratorFactory;
        $this->_moduleReader = $moduleReader;
        $this->componentDirSearch = $componentDirSearch;
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
                $themePaths = $this->componentDirSearch->collectFiles(ComponentRegistrar::THEME, 'etc/' . $filename);
                $iterator = $this->iteratorFactory->create($themePaths);
                break;
            default:
                $iterator = $this->iteratorFactory->create([]);
                break;
        }
        return $iterator;
    }
}

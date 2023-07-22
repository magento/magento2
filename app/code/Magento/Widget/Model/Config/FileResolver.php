<?php
/**
 * Application config file resolver
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Widget\Model\Config;

use Magento\Framework\Component\DirSearch;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Config\FileIteratorFactory;
use Magento\Framework\Config\FileResolverInterface;
use Magento\Framework\Module\Dir\Reader as DirReader;

class FileResolver implements FileResolverInterface
{
    /**
     * Module configuration file reader
     *
     * @var DirReader
     */
    protected $_moduleReader;

    /**
     * @param DirReader $moduleReader
     * @param FileIteratorFactory $iteratorFactory
     * @param DirSearch $componentDirSearch
     */
    public function __construct(
        DirReader $moduleReader,
        protected readonly FileIteratorFactory $iteratorFactory,
        private readonly DirSearch $componentDirSearch
    ) {
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

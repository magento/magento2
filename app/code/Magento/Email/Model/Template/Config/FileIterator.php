<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Email\Model\Template\Config;

use Magento\Framework\Filesystem\DriverPool;
use Magento\Framework\Filesystem\File\ReadFactory;

/**
 * Class FileIterator
 */
class FileIterator extends \Magento\Framework\Config\FileIterator
{
    /**
     * @var \Magento\Framework\Module\Dir\ReverseResolver
     */
    protected $_moduleDirResolver;

    /**
     * @param ReadFactory $readFactory
     * @param array $paths
     * @param \Magento\Framework\Module\Dir\ReverseResolver $dirResolver
     */
    public function __construct(
        ReadFactory $readFactory,
        array $paths,
        \Magento\Framework\Module\Dir\ReverseResolver $dirResolver
    ) {
        parent::__construct($readFactory, $paths);
        $this->_moduleDirResolver = $dirResolver;
    }

    /**
     * @return string
     * @throws \UnexpectedValueException
     */
    public function current()
    {
        $path = $this->key();
        $moduleName = $this->_moduleDirResolver->getModuleName($path);
        if (!$moduleName) {
            throw new \UnexpectedValueException(
                sprintf("Unable to determine a module, file '%s' belongs to.", $this->key())
            );
        }

        /** @var \Magento\Framework\Filesystem\File\Read $fileRead */
        $fileRead = $this->fileReadFactory->create($this->key(), DriverPool::FILE);
        $contents = $fileRead->readAll();
        return str_replace('<template ', '<template module="' . $moduleName . '" ', $contents);
    }
}

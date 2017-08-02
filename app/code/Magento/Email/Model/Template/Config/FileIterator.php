<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Email\Model\Template\Config;

use Magento\Framework\Filesystem\DriverPool;
use Magento\Framework\Filesystem\File\ReadFactory;

/**
 * Class FileIterator
 * @since 2.0.0
 */
class FileIterator extends \Magento\Framework\Config\FileIterator
{
    /**
     * @var \Magento\Framework\Module\Dir\ReverseResolver
     * @since 2.0.0
     */
    protected $_moduleDirResolver;

    /**
     * @param ReadFactory $readFactory
     * @param array $paths
     * @param \Magento\Framework\Module\Dir\ReverseResolver $dirResolver
     * @since 2.0.0
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
     * @since 2.0.0
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

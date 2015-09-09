<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Email\Model\Template\Config;

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
     * @param \Magento\Framework\Filesystem\DriverInterface $filesystemDriver
     * @param array $paths
     * @param \Magento\Framework\Module\Dir\ReverseResolver $dirResolver
     */
    public function __construct(
        \Magento\Framework\Filesystem\DriverInterface $filesystemDriver,
        array $paths,
        \Magento\Framework\Module\Dir\ReverseResolver $dirResolver
    ) {
        parent::__construct($filesystemDriver, $paths);
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
        $contents = $this->filesystemDriver->fileGetContents($this->key());
        return str_replace('<template ', '<template module="' . $moduleName . '" ', $contents);
    }
}

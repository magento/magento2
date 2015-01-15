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
     * @param \Magento\Framework\Filesystem\Directory\ReadInterface $directory
     * @param array $paths
     * @param \Magento\Framework\Module\Dir\ReverseResolver $dirResolver
     */
    public function __construct(
        \Magento\Framework\Filesystem\Directory\ReadInterface $directory,
        array $paths,
        \Magento\Framework\Module\Dir\ReverseResolver $dirResolver
    ) {
        parent::__construct($directory, $paths);
        $this->_moduleDirResolver = $dirResolver;
    }

    /**
     * @return string
     * @throws \UnexpectedValueException
     */
    public function current()
    {
        $path = $this->directoryRead->getAbsolutePath($this->key());
        $moduleName = $this->_moduleDirResolver->getModuleName($path);
        if (!$moduleName) {
            throw new \UnexpectedValueException(
                sprintf("Unable to determine a module, file '%s' belongs to.", $this->key())
            );
        }
        $contents = $this->directoryRead->readFile($this->key());
        return str_replace('<template ', '<template module="' . $moduleName . '" ', $contents);
    }
}

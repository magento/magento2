<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Extended version of \Magento\Framework\Archive\Tar that supports filtering
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Framework\Backup\Archive;

/**
 * Class \Magento\Framework\Backup\Archive\Tar
 *
 * @since 2.0.0
 */
class Tar extends \Magento\Framework\Archive\Tar
{
    /**
     * Filenames or filename parts that are used for filtering files
     *
     * @var array
     * @since 2.0.0
     */
    protected $_skipFiles = [];

    /**
     * Overridden \Magento\Framework\Archive\Tar::_createTar method that does the same actions as it's parent but
     * filters files using \Magento\Framework\Backup\Filesystem\Iterator\Filter
     *
     * @param bool $skipRoot
     * @param bool $finalize
     * @return void
     *
     * @see \Magento\Framework\Archive\Tar::_createTar()
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.0.0
     */
    protected function _createTar($skipRoot = false, $finalize = false)
    {
        $path = $this->_getCurrentFile();

        $filesystemIterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        $iterator = new \Magento\Framework\Backup\Filesystem\Iterator\Filter(
            $filesystemIterator,
            $this->_skipFiles
        );

        foreach ($iterator as $item) {
            $this->_setCurrentFile($item->getPathname());
            $this->_packAndWriteCurrentFile();
        }

        if ($finalize) {
            $this->_getWriter()->write(str_repeat("\0", self::TAR_BLOCK_SIZE * 12));
        }
    }

    /**
     * Set files that shouldn't be added to tarball
     *
     * @param array $skipFiles
     * @return $this
     * @since 2.0.0
     */
    public function setSkipFiles(array $skipFiles)
    {
        $this->_skipFiles = $skipFiles;
        return $this;
    }
}

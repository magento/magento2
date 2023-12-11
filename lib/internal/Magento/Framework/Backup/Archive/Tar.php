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

use Magento\Framework\Backup\Filesystem\Iterator\Filter;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Class to work with tar archives
 */
class Tar extends \Magento\Framework\Archive\Tar
{
    /**
     * Filenames or filename parts that are used for filtering files
     *
     * @var array
     */
    protected $_skipFiles = [];

    /**
     *  Method same as it's parent but filters files using \Magento\Framework\Backup\Filesystem\Iterator\Filter
     *
     * @param bool $skipRoot
     * @param bool $finalize
     * @return void
     *
     * @see \Magento\Framework\Archive\Tar::_createTar()
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _createTar($skipRoot = false, $finalize = false)
    {
        $path = $this->_getCurrentFile();
        $filesystemIterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::FOLLOW_SYMLINKS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        $iterator = new Filter(
            $filesystemIterator,
            $this->_skipFiles
        );

        foreach ($iterator as $item) {
            // exclude symlinks to do not get duplicates after follow symlinks in RecursiveDirectoryIterator
            if ($item->isLink()) {
                continue;
            }
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
     */
    public function setSkipFiles(array $skipFiles)
    {
        $this->_skipFiles = $skipFiles;
        return $this;
    }
}

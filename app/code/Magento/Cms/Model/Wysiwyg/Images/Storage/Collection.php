<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Cms\Model\Wysiwyg\Images\Storage;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Wysiwyg Images storage collection
 *
 * @api
 * @since 2.0.0
 */
class Collection extends \Magento\Framework\Data\Collection\Filesystem
{
    /**
     * @var \Magento\Framework\Filesystem
     * @since 2.0.0
     */
    protected $_filesystem;

    /**
     * @param \Magento\Framework\Data\Collection\EntityFactory $entityFactory
     * @param \Magento\Framework\Filesystem $filesystem
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        \Magento\Framework\Filesystem $filesystem
    ) {
        $this->_filesystem = $filesystem;
        parent::__construct($entityFactory);
    }

    /**
     * Generate row
     *
     * @param string $filename
     * @return array
     * @since 2.0.0
     */
    protected function _generateRow($filename)
    {
        $filename = preg_replace('~[/\\\]+~', '/', $filename);
        $path = $this->_filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        return [
            'filename' => $filename,
            'basename' => basename($filename),
            'mtime' => $path->stat($path->getRelativePath($filename))['mtime']
        ];
    }
}

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
 * @since 100.0.2
 */
class Collection extends \Magento\Framework\Data\Collection\Filesystem
{
    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $_filesystem;

    /**
     * @param \Magento\Framework\Data\Collection\EntityFactory $entityFactory
     * @param \Magento\Framework\Filesystem $filesystem
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        \Magento\Framework\Filesystem $filesystem
    ) {
        $this->_filesystem = $filesystem;
        parent::__construct($entityFactory, $filesystem);
    }

    /**
     * Generate row
     *
     * @param string $filename
     * @return array
     */
    protected function _generateRow($filename)
    {
        $filename = preg_replace('~[/\\\]+(?<![htps?]://)~', '/', $filename);
        $path = $this->_filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        return [
            'filename' => rtrim($filename, '/'),
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            'basename' => basename($filename),
            'mtime' => $path->stat($path->getRelativePath($filename))['mtime']
        ];
    }
}

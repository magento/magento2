<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Model\Export\Adapter;

use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\DirectoryList;

/**
 * Abstract adapter model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
abstract class AbstractAdapter
{
    /**
     * Destination file path.
     *
     * @var string
     */
    protected $_destination;

    /**
     * Header columns names.
     *
     * @var array
     */
    protected $_headerCols = null;

    /**
     * @var \Magento\Framework\Filesystem\Directory\Write
     */
    protected $_directoryHandle;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Filesystem $filesystem
     * @param string|null $destination
     * @throws \Magento\Framework\Model\Exception
     */
    public function __construct(\Magento\Framework\Filesystem $filesystem, $destination = null)
    {
        $this->_directoryHandle = $filesystem->getDirectoryWrite(DirectoryList::SYS_TMP);
        if (!$destination) {
            $destination = uniqid('importexport_');
            $this->_directoryHandle->touch($destination);
        }
        if (!is_string($destination)) {
            throw new \Magento\Framework\Model\Exception(__('Destination file path must be a string'));
        }

        if (!$this->_directoryHandle->isWritable()) {
            throw new \Magento\Framework\Model\Exception(__('Destination directory is not writable'));
        }
        if ($this->_directoryHandle->isFile($destination) && !$this->_directoryHandle->isWritable($destination)) {
            throw new \Magento\Framework\Model\Exception(__('Destination file is not writable'));
        }

        $this->_destination = $destination;

        $this->_init();
    }

    /**
     * Method called as last step of object instance creation. Can be overridden in child classes.
     *
     * @return $this
     */
    protected function _init()
    {
        return $this;
    }

    /**
     * Get contents of export file
     *
     * @return string
     */
    public function getContents()
    {
        return $this->_directoryHandle->readFile($this->_destination);
    }

    /**
     * MIME-type for 'Content-Type' header
     *
     * @return string
     */
    public function getContentType()
    {
        return 'application/octet-stream';
    }

    /**
     * Return file extension for downloading
     *
     * @return string
     */
    public function getFileExtension()
    {
        return '';
    }

    /**
     * Set column names
     *
     * @param array $headerColumns
     * @return $this
     */
    public function setHeaderCols(array $headerColumns)
    {
        return $this;
    }

    /**
     * Write row data to source file
     *
     * @param array $rowData
     * @return $this
     */
    abstract public function writeRow(array $rowData);
}

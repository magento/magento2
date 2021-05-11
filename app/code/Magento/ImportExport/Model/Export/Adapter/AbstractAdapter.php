<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Model\Export\Adapter;

use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Abstract adapter model
 *
 * @api
 * @since 100.0.2
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
     * @param Filesystem $filesystem
     * @param string|null $destination
     * @param string $destinationDirectoryCode
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function __construct(
        \Magento\Framework\Filesystem $filesystem,
        $destination = null,
        $destinationDirectoryCode = DirectoryList::VAR_IMPORT_EXPORT
    ) {
        $this->_directoryHandle = $filesystem->getDirectoryWrite($destinationDirectoryCode);
        if (!$destination) {
            $destination = uniqid('importexport_');
            $this->_directoryHandle->touch($destination);
        }
        if (!is_string($destination)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('The destination file path must be a string.')
            );
        }

        if (!$this->_directoryHandle->isWritable()) {
            throw new \Magento\Framework\Exception\LocalizedException(__('The destination directory is not writable.'));
        }
        if ($this->_directoryHandle->isFile($destination) && !$this->_directoryHandle->isWritable($destination)) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Destination file is not writable'));
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
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
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

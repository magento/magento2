<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Model\Import\Source;

/**
 * CSV import adapter
 */
class Csv extends \Magento\ImportExport\Model\Import\AbstractSource
{
    /**
     * @var \Magento\Framework\Filesystem\File\Write
     */
    protected $_file;

    /**
     * @var string
     */
    protected $_delimiter = '';

    /**
     * @var string
     */
    protected $_enclosure = '';

    /**
     * Open file and detect column names
     *
     * There must be column names in the first line
     *
     * @param string $file
     * @param \Magento\Framework\Filesystem\Directory\Write $directory
     * @param string $delimiter
     * @param string $enclosure
     * @throws \LogicException
     */
    public function __construct(
        $file,
        \Magento\Framework\Filesystem\Directory\Write $directory,
        $delimiter = ',',
        $enclosure = '"'
    ) {
        try {
            $this->_file = $directory->openFile($directory->getRelativePath($file), 'r');
        } catch (\Magento\Framework\Filesystem\FilesystemException $e) {
            throw new \LogicException("Unable to open file: '{$file}'");
        }
        $this->_delimiter = $delimiter;
        $this->_enclosure = $enclosure;
        parent::__construct($this->_getNextRow());
    }

    /**
     * Close file handle
     */
    public function __destruct()
    {
        if (is_object($this->_file)) {
            $this->_file->close();
        }
    }

    /**
     * Read next line from CSV-file
     *
     * @return array|bool
     */
    protected function _getNextRow()
    {
        return $this->_file->readCsv(0, $this->_delimiter, $this->_enclosure);
    }

    /**
     * Rewind the \Iterator to the first element (\Iterator interface)
     *
     * @return void
     */
    public function rewind()
    {
        $this->_file->seek(0);
        $this->_getNextRow();
        // skip first line with the header
        parent::rewind();
    }
}

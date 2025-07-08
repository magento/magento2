<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Model\Import\Source;

use Magento\Framework\Filesystem\Directory\Read as DirectoryRead;
use Magento\Framework\Filesystem\File\ReadInterface as FileReadInterface;

/**
 * CSV import adapter
 */
class Csv extends \Magento\ImportExport\Model\Import\AbstractSource
{
    /**
     * @var FileReadInterface
     */
    protected $_file;

    /**
     * @var string
     */
    protected $_delimiter = ',';

    /**
     * @var string
     */
    protected $_enclosure = '';

    /**
     * @var string
     */
    private string $filePath;

    /**
     * @var array
     */
    private static array $openFiles;

    /**
     * Open file and detect column names
     *
     * There must be column names in the first line
     *
     * @param string|FileReadInterface $file
     * @param DirectoryRead $directory
     * @param string $delimiter
     * @param string $enclosure
     * @throws \LogicException
     */
    public function __construct(
        $file,
        DirectoryRead $directory,
        $delimiter = ',',
        $enclosure = '"'
    ) {
        if ($file instanceof FileReadInterface) {
            $this->filePath = '';
            $this->_file = $file;
            $this->_file->seek(0);
        } else {
            try {
                $this->filePath = $directory->getRelativePath($file);
                $this->_file = $directory->openFile($this->filePath, 'r');
                $this->_file->seek(0);
                self::$openFiles[$this->filePath] = true;
            } catch (\Magento\Framework\Exception\FileSystemException $e) {
                throw new \LogicException("Unable to open file: '{$file}'");
            }
        }
        if ($delimiter) {
            $this->_delimiter = $delimiter;
        }
        $this->_enclosure = $enclosure;
        parent::__construct($this->_getNextRow());
    }

    /**
     * Close file handle
     *
     * @return void
     */
    public function __destruct()
    {
        if (is_object($this->_file) && !empty(self::$openFiles[$this->filePath])) {
            $this->_file->close();
            unset(self::$openFiles[$this->filePath]);
        }
    }

    /**
     * Read next line from CSV-file
     *
     * @return array|bool
     */
    protected function _getNextRow()
    {
        $parsed = $this->_file->readCsv(0, $this->_delimiter, $this->_enclosure);
        if (is_array($parsed) && count($parsed) != $this->_colQty) {
            foreach ($parsed as $element) {
                if ($element && strpos($element, "'") !== false) {
                    $this->_foundWrongQuoteFlag = true;
                    break;
                }
            }
        } else {
            $this->_foundWrongQuoteFlag = false;
        }
        return is_array($parsed) ? $parsed : [];
    }

    /**
     * Rewind the \Iterator to the first element (\Iterator interface)
     *
     * @return void
     */
    #[\ReturnTypeWillChange]
    public function rewind()
    {
        $this->_file->seek(0);
        $this->_getNextRow();
        // skip first line with the header
        parent::rewind();
    }
}

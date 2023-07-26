<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Model\Import\Source;

use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Filesystem\Directory\Read as DirectoryRead;
use Magento\Framework\Filesystem\File\ReadInterface as FileReadInterface;

/**
 * JSON import adapter
 */
class Json extends \Magento\ImportExport\Model\Import\AbstractSource
{
    /**
     * @var FileReadInterface
     */
    private FileReadInterface $_file;

    /**
     * @var array
     */
    private array $items;

    /**
     * @var int
     */
    private int $position = 0;

    /**
     * @var array|int[]|string[] $colNames
     */
    private array $colNames = [];

    /**
     * Open file and decode JSON data
     *
     * @param string|FileReadInterface $file
     * @param DirectoryRead $directory
     * @throws ValidatorException
     */
    public function __construct(
        $file,
        DirectoryRead $directory
    ) {
        if ($file instanceof FileReadInterface) {
            $this->_file = $file;
            $this->_file->seek(0);
        } else {
            try {
                $filePath = $directory->getRelativePath($file);
                $this->_file = $directory->openFile($filePath, 'r');
                $this->_file->seek(0);
            } catch (\Magento\Framework\Exception\FileSystemException $e) {
                throw new \LogicException("Unable to open file: '{$file}'");
            }
        }
        $jsonData = '';
        while (!$this->_file->eof()) {
            $chunk = $this->_file->read(1024);
            $jsonData .= $chunk;
        }
        $this->items = json_decode($jsonData, true) ?: [];
        // convert all scalar values to strings
        $this->items = array_map(function ($item) {
            return array_map(function ($value) {
                return is_scalar($value) ? strval($value) : $value;
            }, $item);
        }, $this->items);
        if (isset($this->items[0])) {
            $this->colNames = array_keys($this->items[0]);
        }
        parent::__construct($this->colNames ?? []);
    }

    /**
     * Read next item from JSON data
     *
     * @return array|bool
     */
    protected function _getNextRow()
    {
        if (isset($this->items[$this->position])) {
            return $this->items[$this->position++];
        }
        return false;
    }

    /**
     * Rewind the \Iterator to the first element (\Iterator interface)
     *
     * @return void
     */
    #[\ReturnTypeWillChange]
    public function rewind()
    {
        $this->position = 0;
        parent::rewind();
    }

    /**
     * Seek to a specific position in the data
     *
     * @param int $position
     * @return void
     */
    public function seek($position)
    {
        $this->position = $position;
        parent::__construct($this->_getNextRow());
    }
}

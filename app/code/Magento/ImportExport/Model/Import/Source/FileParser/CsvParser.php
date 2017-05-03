<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ImportExport\Model\Import\Source\FileParser;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;

class CsvParser implements ParserInterface
{
    /**
     * List of CSV parsing options
     *
     * @var array
     */
    private $options = [
        'delimiter' => ',',
        'enclosure' => '"',
        'escape' => '\\',
        'null' => null
    ];

    /**
     * File reader
     *
     * @var Filesystem\File\ReadInterface
     */
    private $file;

    /**
     * Columns of CSV file
     *
     * @var string[]
     */
    private $columns;

    public function __construct(
        Filesystem\File\ReadInterface $file,
        $options = []
    ) {
        $this->file = $file;
        $this->options = $options + $this->options;
        $this->columns = $this->fetchCsvLine();

        if ($this->columns === false) {
            throw new \InvalidArgumentException('CSV file should contain at least 1 row');
        }
    }

    public function getColumnNames()
    {
        return $this->columns;
    }

    public function fetchRow()
    {
        $row = $this->fetchCsvLine();

        if ($row === false) {
            return false;
        }

        return $this->mapRowData($row);
    }

    public function reset()
    {
        $this->file->seek(0);
        $this->fetchCsvLine();
    }

    private function fetchCsvLine()
    {
        return $this->file->readCsv(
            0,
            $this->options['delimiter'],
            $this->options['enclosure'],
            $this->options['escape']
        );
    }

    private function mapRowData($row)
    {
        $result = [];
        foreach ($this->columns as $index => $column) {
            $result[] = $this->mapNullValue(
                $this->extractRowValue($row, $index)
            );
        }
        return $result;
    }

    public function __destruct()
    {
        $this->file->close();
    }

    private function mapNullValue($value)
    {
        if ($value === $this->options['null']) {
            $value = null;
        }
        return $value;
    }

    private function extractRowValue($row, $index)
    {
        return (isset($row[$index]) ? $row[$index] : '');
    }
}

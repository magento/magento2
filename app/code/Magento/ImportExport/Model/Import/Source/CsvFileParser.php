<?php
/**
 * magento-2-contribution-day
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.
 *
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/MIT
 *
 * @copyright  Copyright (c) 2017 EcomDev BV (http://www.ecomdev.org)
 * @license    https://opensource.org/licenses/MIT The MIT License (MIT)
 * @author     Ivan Chepurnyi <ivan@ecomdev.org>
 */


namespace Magento\ImportExport\Model\Import\Source;


use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;

class CsvFileParser implements FileParserInterface
{
    /**
     * Enclosure for values in CSV file
     *
     * @var string
     */
    private $enclosure;

    /**
     * Column delimiter
     *
     * @var string
     */
    private $delimiter;

    /**
     * Escape sequence character
     *
     * @var string
     */
    private $escape;

    /**
     * Null character for parsing value
     *
     * @var string
     */
    private $nullPlaceholder;

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
        $filePath,
        Filesystem $filesystem,
        $directoryCode = DirectoryList::ROOT,
        $nullPlaceholder = null,
        $enclosure = '"',
        $delimiter = ',',
        $escape = '\\'
    ) {
        $this->nullPlaceholder = $nullPlaceholder;
        $this->enclosure = $enclosure;
        $this->delimiter = $delimiter;
        $this->escape = $escape;

        $directory = $filesystem->getDirectoryRead($directoryCode);
        if (!$directory->isFile($filePath)) {
            throw new \InvalidArgumentException(
                sprintf('File "%s" does not exists', $filePath)
            );
        }

        $this->file = $directory->openFile($filePath);
        $this->columns = $this->fetchCsvLine();
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
        return $this->file->readCsv(0, $this->delimiter, $this->enclosure, $this->escape);
    }

    private function mapRowData($row)
    {
        $result = [];
        foreach ($this->columns as $index => $column) {
            $value = isset($row[$index]) ? $row[$index] : '';

            if ($this->nullPlaceholder && $value === $this->nullPlaceholder) {
                $value = null;
            }

            $result[] = $value;
        }
        return $result;
    }

}

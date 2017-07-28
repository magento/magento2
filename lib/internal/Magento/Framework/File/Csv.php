<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\File;

use Magento\Framework\Filesystem\Driver\File;

/**
 * Csv parse
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class Csv
{
    /**
     * @var int
     * @since 2.0.0
     */
    protected $_lineLength = 0;

    /**
     * @var string
     * @since 2.0.0
     */
    protected $_delimiter = ',';

    /**
     * @var string
     * @since 2.0.0
     */
    protected $_enclosure = '"';

    /**
     * @var File
     * @since 2.0.0
     */
    protected $file;

    /**
     * Constructor
     *
     * @param File $file File Driver used for writing CSV
     * @since 2.0.0
     */
    public function __construct(File $file)
    {
        $this->file = $file;
    }

    /**
     * Set max file line length
     *
     * @param   int $length
     * @return  \Magento\Framework\File\Csv
     * @since 2.0.0
     */
    public function setLineLength($length)
    {
        $this->_lineLength = $length;
        return $this;
    }

    /**
     * Set CSV column delimiter
     *
     * @param   string $delimiter
     * @return  \Magento\Framework\File\Csv
     * @since 2.0.0
     */
    public function setDelimiter($delimiter)
    {
        $this->_delimiter = $delimiter;
        return $this;
    }

    /**
     * Set CSV column value enclosure
     *
     * @param   string $enclosure
     * @return  \Magento\Framework\File\Csv
     * @since 2.0.0
     */
    public function setEnclosure($enclosure)
    {
        $this->_enclosure = $enclosure;
        return $this;
    }

    /**
     * Retrieve CSV file data as array
     *
     * @param   string $file
     * @return  array
     * @throws \Exception
     * @since 2.0.0
     */
    public function getData($file)
    {
        $data = [];
        if (!file_exists($file)) {
            throw new \Exception('File "' . $file . '" does not exist');
        }

        $fh = fopen($file, 'r');
        while ($rowData = fgetcsv($fh, $this->_lineLength, $this->_delimiter, $this->_enclosure)) {
            $data[] = $rowData;
        }
        fclose($fh);
        return $data;
    }

    /**
     * Retrieve CSV file data as pairs
     *
     * @param   string $file
     * @param   int $keyIndex
     * @param   int $valueIndex
     * @return  array
     * @since 2.0.0
     */
    public function getDataPairs($file, $keyIndex = 0, $valueIndex = 1)
    {
        $data = [];
        $csvData = $this->getData($file);
        foreach ($csvData as $rowData) {
            if (isset($rowData[$keyIndex])) {
                $data[$rowData[$keyIndex]] = isset($rowData[$valueIndex]) ? $rowData[$valueIndex] : null;
            }
        }
        return $data;
    }

    /**
     * Saving data row array into file
     *
     * @param   string $file
     * @param   array $data
     * @return  $this
     * @since 2.0.0
     */
    public function saveData($file, $data)
    {
        $fh = fopen($file, 'w');
        foreach ($data as $dataRow) {
            $this->file->filePutCsv($fh, $dataRow, $this->_delimiter, $this->_enclosure);
        }
        fclose($fh);
        return $this;
    }
}

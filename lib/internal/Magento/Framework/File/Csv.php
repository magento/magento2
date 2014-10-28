<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright  Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\File;

/**
 * Csv parse
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Csv
{
    /**
     * @var int
     */
    protected $_lineLength = 0;

    /**
     * @var string
     */
    protected $_delimiter = ',';

    /**
     * @var string
     */
    protected $_enclosure = '"';

    /**
     * Constructor
     */
    public function __construct()
    {
    }

    /**
     * Set max file line length
     *
     * @param   int $length
     * @return  \Magento\Framework\File\Csv
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
     */
    public function getData($file)
    {
        $data = array();
        if (!file_exists($file)) {
            throw new \Exception('File "' . $file . '" do not exists');
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
     */
    public function getDataPairs($file, $keyIndex = 0, $valueIndex = 1)
    {
        $data = array();
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
     */
    public function saveData($file, $data)
    {
        $fh = fopen($file, 'w');
        foreach ($data as $dataRow) {
            $this->fputcsv($fh, $dataRow, $this->_delimiter, $this->_enclosure);
        }
        fclose($fh);
        return $this;
    }

    /**
     * Write to csv
     *
     * @param resource $handle
     * @param string[] $fields
     * @param string $delimiter
     * @param string $enclosure
     * @return int
     */
    public function fputcsv(&$handle, $fields = array(), $delimiter = ',', $enclosure = '"')
    {
        $str = '';
        $escape_char = '\\';
        foreach ($fields as $value) {
            if (strpos(
                $value,
                $delimiter
            ) !== false || strpos(
                $value,
                $enclosure
            ) !== false || strpos(
                $value,
                "\n"
            ) !== false || strpos(
                $value,
                "\r"
            ) !== false || strpos(
                $value,
                "\t"
            ) !== false || strpos(
                $value,
                ' '
            ) !== false
            ) {
                $str2 = $enclosure;
                $escaped = 0;
                $len = strlen($value);
                for ($i = 0; $i < $len; $i++) {
                    if ($value[$i] == $escape_char) {
                        $escaped = 1;
                    } else if (!$escaped && $value[$i] == $enclosure) {
                        $str2 .= $enclosure;
                    } else {
                        $escaped = 0;
                    }
                    $str2 .= $value[$i];
                }
                $str2 .= $enclosure;
                $str .= $str2 . $delimiter;
            } else {
                $str .= $enclosure . $value . $enclosure . $delimiter;
            }
        }
        $str = substr($str, 0, -1);
        $str .= "\n";
        return fwrite($handle, $str);
    }
}

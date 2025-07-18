<?php
/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */

/**
 * Csv parse
 */
namespace Magento\Framework\File;

class CsvMulty extends \Magento\Framework\File\Csv
{
    /**
     * Retrieve CSV file data as pairs with duplicates
     *
     * @param   string $file
     * @param   int $keyIndex
     * @param   int $valueIndex
     * @return  array
     */
    public function getDataPairs($file, $keyIndex = 0, $valueIndex = 1)
    {
        $data = [];
        $csvData = $this->getData($file);
        $lineNumber = 0;
        foreach ($csvData as $rowData) {
            $lineNumber++;
            if (isset($rowData[$keyIndex])) {
                if (isset($data[$rowData[$keyIndex]])) {
                    if (isset($data[$rowData[$keyIndex]]['duplicate'])) {
                        $data[$rowData[$keyIndex]]['duplicate']['line'] .= ', ' . $lineNumber;
                    } else {
                        $tmpValue = $data[$rowData[$keyIndex]]['value'];
                        $tmpLine = $data[$rowData[$keyIndex]]['line'];
                        $data[$rowData[$keyIndex]]['duplicate'] = [];
                        $data[$rowData[$keyIndex]]['duplicate']['line'] = $tmpLine . ' ,' . $lineNumber;
                        $data[$rowData[$keyIndex]]['duplicate']['value'] = $tmpValue;
                    }
                } else {
                    $data[$rowData[$keyIndex]] = [];
                    $data[$rowData[$keyIndex]]['line'] = $lineNumber;
                    $data[$rowData[$keyIndex]]['value'] = isset($rowData[$valueIndex]) ? $rowData[$valueIndex] : null;
                }
            }
        }
        return $data;
    }
}

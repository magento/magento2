<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Convert;

use Magento\Framework\Filesystem\File\WriteInterface;

/**
 * Convert the data to XML Excel
 */
class Excel
{
    /**
     * \ArrayIterator Object
     *
     * @var \Iterator|null
     */
    protected $_iterator = null;

    /**
     * Method Callback Array
     *
     * @var array
     */
    protected $_rowCallback = [];

    /**
     * Grid Header Array
     *
     * @var array
     */
    protected $_dataHeader = [];

    /**
     * Grid Footer Array
     *
     * @var array
     */
    protected $_dataFooter = [];

    /**
     * Class Constructor
     *
     * @param \Iterator $iterator
     * @param array $rowCallback
     */
    public function __construct(\Iterator $iterator, $rowCallback = [])
    {
        $this->_iterator = $iterator;
        $this->_rowCallback = $rowCallback;
    }

    /**
     * Retrieve Excel XML Document Header XML Fragment
     * Append data header if it is available
     *
     * @param string $sheetName
     * @return string
     */
    protected function _getXmlHeader($sheetName = '')
    {
        if (empty($sheetName)) {
            $sheetName = 'Sheet 1';
        }

        $sheetName = htmlspecialchars($sheetName);

        $xmlHeader = '<' .
            '?xml version="1.0"?' .
            '><' .
            '?mso-application progid="Excel.Sheet"?' .
            '><Workbook' .
            ' xmlns="urn:schemas-microsoft-com:office:spreadsheet"' .
            ' xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"' .
            ' xmlns:x="urn:schemas-microsoft-com:office:excel"' .
            ' xmlns:x2="http://schemas.microsoft.com/office/excel/2003/xml"' .
            ' xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"' .
            ' xmlns:o="urn:schemas-microsoft-com:office:office"' .
            ' xmlns:html="http://www.w3.org/TR/REC-html40"' .
            ' xmlns:c="urn:schemas-microsoft-com:office:component:spreadsheet">' .
            '<OfficeDocumentSettings xmlns="urn:schemas-microsoft-com:office:office">' .
            '</OfficeDocumentSettings>' .
            '<ExcelWorkbook xmlns="urn:schemas-microsoft-com:office:excel">' .
            '</ExcelWorkbook>' .
            '<Worksheet ss:Name="' .
            $sheetName .
            '">' .
            '<Table>';

        if ($this->_dataHeader) {
            $xmlHeader .= $this->_getXmlRow($this->_dataHeader, false);
        }

        return $xmlHeader;
    }

    /**
     * Retrieve Excel XML Document Footer XML Fragment
     * Append data footer if it is available
     *
     * @return string
     */
    protected function _getXmlFooter()
    {
        $xmlFooter = '';

        if ($this->_dataFooter) {
            $xmlFooter = $this->_getXmlRow($this->_dataFooter, false);
        }

        $xmlFooter .= '</Table></Worksheet></Workbook>';

        return $xmlFooter;
    }

    /**
     * Get a Single XML Row
     *
     * @param array $row
     * @param boolean $useCallback
     * @return string
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _getXmlRow($row, $useCallback)
    {
        if ($useCallback && $this->_rowCallback) {
            $row = call_user_func($this->_rowCallback, $row);
        }
        $xmlData = [];
        $xmlData[] = '<Row>';

        foreach ($row as $value) {
            $value = htmlspecialchars($value);
            $dataType = is_numeric($value) && $value[0] !== '+' && $value[0] !== '0' ? 'Number' : 'String';

            /**
             * Security enhancement for CSV data processing by Excel-like applications.
             * @see https://bugzilla.mozilla.org/show_bug.cgi?id=1054702
             *
             * @var $value string|\Magento\Framework\Phrase
             */
            if (!is_string($value)) {
                $value = (string)$value;
            }
            if (isset($value[0]) && in_array($value[0], ['=', '+', '-'])) {
                $value = ' ' . $value;
            }

            $value = str_replace("\r\n", '&#10;', $value);
            $value = str_replace("\r", '&#10;', $value);
            $value = str_replace("\n", '&#10;', $value);

            $xmlData[] = '<Cell><Data ss:Type="' . $dataType . '">' . $value . '</Data></Cell>';
        }
        $xmlData[] = '</Row>';

        return join('', $xmlData);
    }

    /**
     * Set Data Header
     *
     * @param array $data
     * @return void
     */
    public function setDataHeader($data)
    {
        $this->_dataHeader = $data;
    }

    /**
     * Set Data Footer
     *
     * @param array $data
     * @return void
     */
    public function setDataFooter($data)
    {
        $this->_dataFooter = $data;
    }

    /**
     * Convert Data to Excel XML Document
     *
     * @param string $sheetName
     * @return string
     */
    public function convert($sheetName = '')
    {
        $xml = $this->_getXmlHeader($sheetName);

        foreach ($this->_iterator as $dataRow) {
            $xml .= $this->_getXmlRow($dataRow, true);
        }
        $xml .= $this->_getXmlFooter();

        return $xml;
    }

    /**
     * Write Converted XML Data to Temporary File
     *
     * @param WriteInterface $stream
     * @param string $sheetName
     * @return void
     */
    public function write(WriteInterface $stream, $sheetName = '')
    {
        $stream->write($this->_getXmlHeader($sheetName));

        foreach ($this->_iterator as $dataRow) {
            $stream->write($this->_getXmlRow($dataRow, true));
        }
        $stream->write($this->_getXmlFooter());
    }
}

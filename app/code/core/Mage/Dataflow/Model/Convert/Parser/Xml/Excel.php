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
 * @category    Mage
 * @package     Mage_Dataflow
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Convert excel xml parser
 *
 * @category   Mage
 * @package    Mage_Dataflow
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Dataflow_Model_Convert_Parser_Xml_Excel extends Mage_Dataflow_Model_Convert_Parser_Abstract
{
    /**
     * Simple Xml object
     *
     * @var SimpleXMLElement
     */
    protected $_xmlElement;

    /**
     * Field list
     *
     * @var array
     */
    protected $_parseFieldNames;

    public function parse()
    {
        $adapterName   = $this->getVar('adapter', null);
        $adapterMethod = $this->getVar('method', 'saveRow');

        if (!$adapterName || !$adapterMethod) {
            $message = Mage::helper('Mage_Dataflow_Helper_Data')->__('Please declare "adapter" and "method" nodes first.');
            $this->addException($message, Mage_Dataflow_Model_Convert_Exception::FATAL);
            return $this;
        }

        try {
            $adapter = Mage::getModel($adapterName);
        }
        catch (Exception $e) {
            $message = Mage::helper('Mage_Dataflow_Helper_Data')->__('Declared adapter %s was not found.', $adapterName);
            $this->addException($message, Mage_Dataflow_Model_Convert_Exception::FATAL);
            return $this;
        }

        if (!is_callable(array($adapter, $adapterMethod))) {
            $message = Mage::helper('Mage_Dataflow_Helper_Data')->__('Method "%s" was not defined in adapter %s.', $adapterMethod, $adapterName);
            $this->addException($message, Mage_Dataflow_Model_Convert_Exception::FATAL);
            return $this;
        }

        $batchModel = $this->getBatchModel();
        $batchIoAdapter = $this->getBatchModel()->getIoAdapter();

        if (Mage::app()->getRequest()->getParam('files')) {
            $file = Mage::app()->getConfig()->getTempVarDir().'/import/'
                . urldecode(Mage::app()->getRequest()->getParam('files'));
            $this->_copy($file);
        }

        $batchIoAdapter->open(false);

        $isFieldNames = $this->getVar('fieldnames', '') == 'true' ? true : false;
        if (!$isFieldNames && is_array($this->getVar('map'))) {
            $this->_parseFieldNames = $this->getVar('map');
        }

        $worksheet = $this->getVar('single_sheet', '');

        $xmlString = $xmlRowString = '';
        $countRows = 0;
        $isWorksheet = $isRow = false;
        while (($xmlOriginalString = $batchIoAdapter->read()) !== false) {
            $xmlString .= $xmlOriginalString;
            if (!$isWorksheet) {
                $strposS = strpos($xmlString, '<Worksheet');
                $substrL = 10;
                //fix for OpenOffice
                if ($strposS === false) {
                    $strposS = strpos($xmlString, '<ss:Worksheet');
                    $substrL = 13;
                }
                if ($strposS === false) {
                    $xmlString = substr($xmlString, -13);
                    continue;
                }

                $xmlTmpString = substr($xmlString, $strposS);
                $strposF = strpos($xmlTmpString, '>');

                if ($strposF === false) {
                    $xmlString = $xmlTmpString;
                    continue;
                }

                if (!$worksheet) {
                    $xmlString = substr($xmlTmpString, $strposF);
                    $isWorksheet = true;
                    continue;
                }
                else {
                    if (preg_match('/ss:Name=\"'.preg_quote($worksheet).'\"/siU', substr($xmlTmpString, 0, $strposF))) {
                        $xmlString = substr($xmlTmpString, $strposF);
                        $isWorksheet = true;
                        continue;
                    }
                    else {
                        $xmlString = '';
                        continue;
                    }
                }
            }
            else {
                $xmlString = $this->_parseXmlRow($xmlString);

                $strposS = strpos($xmlString, '</Worksheet>');
                $substrL = 12;
                //fix for OpenOffice
                if ($strposS === false) {
                    $strposS = strpos($xmlString, '</ss:Worksheet>');
                    $substrL = 15;
                }
                if ($strposS !== false) {
                    $xmlString = substr($xmlString, $strposS + $substrL);
                    $isWorksheet = false;

                    continue;
                }
            }
        }

        $this->addException(Mage::helper('Mage_Dataflow_Helper_Data')->__('Found %d rows.', $this->_countRows));
        $this->addException(Mage::helper('Mage_Dataflow_Helper_Data')->__('Starting %s :: %s', $adapterName, $adapterMethod));

        $batchModel->setParams($this->getVars())
            ->setAdapter($adapterName)
            ->save();

//        $adapter->$adapterMethod();

        return $this;

        $dom = new DOMDocument();
//        $dom->loadXML($this->getData());
        if (Mage::app()->getRequest()->getParam('files')) {
            $path = Mage::app()->getConfig()->getTempVarDir().'/import/';
            $file = $path.urldecode(Mage::app()->getRequest()->getParam('files'));
            if (file_exists($file)) {
                $dom->load($file);
            }
        } else {

            $this->validateDataString();
            $dom->loadXML($this->getData());
        }

        $worksheets = $dom->getElementsByTagName('Worksheet');
        if ($this->getVar('adapter') && $this->getVar('method')) {
            $adapter = Mage::getModel($this->getVar('adapter'));
        }
        foreach ($worksheets as $worksheet) {
            $wsName = $worksheet->getAttribute('ss:Name');
            $rows = $worksheet->getElementsByTagName('Row');
            $firstRow = true;
            $fieldNames = array();
            $wsData = array();
            $i = 0;
            foreach ($rows as $rowSet) {
                $index = 1;
                $cells = $rowSet->getElementsByTagName('Cell');
                $rowData = array();
                foreach ($cells as $cell) {
                    $value = $cell->getElementsByTagName('Data')->item(0)->nodeValue;
                    $ind = $cell->getAttribute('ss:Index');
                    if (!is_null($ind) && $ind>0) {
                        $index = $ind;
                    }
                    if ($firstRow && !$this->getVar('fieldnames')) {
                        $fieldNames[$index] = 'column'.$index;
                    }
                    if ($firstRow && $this->getVar('fieldnames')) {
                        $fieldNames[$index] = $value;
                    } else {
                        $rowData[$fieldNames[$index]] = $value;
                    }
                    $index++;
                }
                $row = $rowData;
                if ($row) {
                    $loadMethod = $this->getVar('method');
                    $adapter->$loadMethod(compact('i', 'row'));
                }
                $i++;

                $firstRow = false;
                if (!empty($rowData)) {
                    $wsData[] = $rowData;
                }
            }
            $data[$wsName] = $wsData;
            $this->addException('Found worksheet "'.$wsName.'" with '.sizeof($wsData).' row(s)');
        }
        if ($wsName = $this->getVar('single_sheet')) {
            if (isset($data[$wsName])) {
                $data = $data[$wsName];
            } else {
                reset($data);
                $data = current($data);
            }
        }
        $this->setData($data);
        return $this;
    }

    /**
     * Parse MS Excel XML string
     *
     * @param string $xmlString
     * @return string
     */
    protected function _parseXmlRow($xmlString)
    {
        $found = true;
        while ($found === true) {
            $strposS = strpos($xmlString, '<Row');

            if ($strposS === false) {
                $found = false;
                continue;
            }

            $xmlTmpString = substr($xmlString, $strposS);
            $strposF = strpos($xmlTmpString, '</Row>');

            if ($strposF !== false) {
                $xmlRowString = substr($xmlTmpString, 0, $strposF + 6);

                $this->_saveParsedRow($xmlRowString);

                $xmlString = substr($xmlTmpString, $strposF + 6);
            }
            else {
                $found = false;
                continue;
            }
        }

        return $xmlString;
    }

    protected function _saveParsedRow($xmlString)
    {
        $xml = '<'.'?xml version="1.0"?'.'><'.'?mso-application progid="Excel.Sheet"?'
            .'><Workbook'
            .' xmlns="urn:schemas-microsoft-com:office:spreadsheet"'
            .' xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"'
            .' xmlns:x="urn:schemas-microsoft-com:office:excel"'
            .' xmlns:x2="http://schemas.microsoft.com/office/excel/2003/xml"'
            .' xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"'
            .' xmlns:o="urn:schemas-microsoft-com:office:office"'
            .' xmlns:html="http://www.w3.org/TR/REC-html40"'
            .' xmlns:c="urn:schemas-microsoft-com:office:component:spreadsheet">'
            . $xmlString
            .'</Workbook>';

        try {
            $xmlElement = new SimpleXMLElement($xml);
        }
        catch (Exception $e) {
            $message = 'Invalid XML row';
            $this->addException($message, Mage_Dataflow_Model_Convert_Exception::ERROR);
            return $this;
        }

        $xmlData  = array();
        $itemData = array();
        $cellIndex = 0;
        foreach ($xmlElement->Row->children() as $cell) {
            if (is_null($this->_parseFieldNames)) {
                $xmlData[(string)$cell->Data] = (string)$cell->Data;
            } else {
                $attributes = $cell->attributes('urn:schemas-microsoft-com:office:spreadsheet');
                if ($attributes && isset($attributes['Index'])) {
                    $cellIndex = $attributes['Index'] - 1;
                }
                $xmlData[$cellIndex] = (string)$cell->Data;
                $cellIndex ++;
            }
        }

        if (is_null($this->_parseFieldNames)) {
            $this->_parseFieldNames = $xmlData;
            return $this;
        }

        $this->_countRows ++;

        $i = 0;
        foreach ($this->_parseFieldNames as $field) {
            $itemData[$field] = isset($xmlData[$i]) ? $xmlData[$i] : null;
            $i ++;
        }

        $batchImportModel = $this->getBatchImportModel()
            ->setId(null)
            ->setBatchId($this->getBatchModel()->getId())
            ->setBatchData($itemData)
            ->setStatus(1)
            ->save();

        return $this;
    }

    public function unparse()
    {
        $batchExport = $this->getBatchExportModel()
            ->setBatchId($this->getBatchModel()->getId());
        $fieldList = $this->getBatchModel()->getFieldList();
        $batchExportIds = $batchExport->getIdCollection();

        if (!is_array($batchExportIds)) {
            return $this;
        }

        $io = $this->getBatchModel()->getIoAdapter();
        $io->open();

        $xml = '<'.'?xml version="1.0"?'.'><'.'?mso-application progid="Excel.Sheet"?'
            .'><Workbook'
            .' xmlns="urn:schemas-microsoft-com:office:spreadsheet"'
            .' xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"'
            .' xmlns:x="urn:schemas-microsoft-com:office:excel"'
            .' xmlns:x2="http://schemas.microsoft.com/office/excel/2003/xml"'
            .' xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"'
            .' xmlns:o="urn:schemas-microsoft-com:office:office"'
            .' xmlns:html="http://www.w3.org/TR/REC-html40"'
            .' xmlns:c="urn:schemas-microsoft-com:office:component:spreadsheet">'
            .'<OfficeDocumentSettings xmlns="urn:schemas-microsoft-com:office:office">'
            .'</OfficeDocumentSettings>'
            .'<ExcelWorkbook xmlns="urn:schemas-microsoft-com:office:excel">'
            .'</ExcelWorkbook>';
        $io->write($xml);

        $wsName = htmlspecialchars($this->getVar('single_sheet'));
        $wsName = !empty($wsName) ? $wsName : Mage::helper('Mage_Dataflow_Helper_Data')->__('Sheet 1');

        $xml = '<Worksheet ss:Name="' . $wsName . '"><Table>';
        $io->write($xml);

        if ($this->getVar('fieldnames')) {
            $xml = $this->_getXmlString($fieldList);
            $io->write($xml);
        }

        foreach ($batchExportIds as $batchExportId) {
            $xmlData = array();
            $batchExport->load($batchExportId);
            $row = $batchExport->getBatchData();

            foreach ($fieldList as $field) {
                $xmlData[] = isset($row[$field]) ? $row[$field] : '';
            }
            $xmlData = $this->_getXmlString($xmlData);
            $io->write($xmlData);
        }

        $xml = '</Table></Worksheet></Workbook>';
        $io->write($xml);
        $io->close();

        return $this;

//        if ($wsName = $this->getVar('single_sheet')) {
//            $data = array($wsName => $this->getData());
//        } else {
//            $data = $this->getData();
//        }
//
//        $this->validateDataGrid();
//
//        $xml = '<'.'?xml version="1.0"?'.'><'.'?mso-application progid="Excel.Sheet"?'.'><Workbook'
//            .' xmlns="urn:schemas-microsoft-com:office:spreadsheet"'
//            .' xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"'
//            .' xmlns:x="urn:schemas-microsoft-com:office:excel"'
//            .' xmlns:x2="http://schemas.microsoft.com/office/excel/2003/xml"'
//            .' xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"'
//            .' xmlns:o="urn:schemas-microsoft-com:office:office"'
//            .' xmlns:html="http://www.w3.org/TR/REC-html40"'
//            .' xmlns:c="urn:schemas-microsoft-com:office:component:spreadsheet">'
//            .'<OfficeDocumentSettings xmlns="urn:schemas-microsoft-com:office:office">'
//            .'</OfficeDocumentSettings>'
//            .'<ExcelWorkbook xmlns="urn:schemas-microsoft-com:office:excel">'
//            .'</ExcelWorkbook>';
//
//        if (is_array($data)) {
//            foreach ($data as $wsName=>$wsData) {
//                if (!is_array($wsData)) {
//                    continue;
//                }
//                $fields = $this->getGridFields($wsData);
//
//                $xml .= '<ss:Worksheet ss:Name="'.$wsName.'"><Table>';
//                if ($this->getVar('fieldnames')) {
//                    $xml .= '<ss:Row>';
//                    foreach ($fields as $fieldName) {
//                        $xml .= '<ss:Cell><Data ss:Type="String">'.$fieldName.'</Data></ss:Cell>';
//                    }
//                    $xml .= '</ss:Row>';
//                }
//                foreach ($wsData as $i=>$row) {
//                    if (!is_array($row)) {
//                        continue;
//                    }
//                    $xml .= '<ss:Row>';
//                    foreach ($fields as $fieldName) {
//                        $data = isset($row[$fieldName]) ? $row[$fieldName] : '';
//                        $xml .= '<ss:Cell><Data ss:Type="String">'.$data.'</Data></ss:Cell>';
//                    }
//                    $xml .= '</ss:Row>';
//                }
//                $xml .= '</Table></ss:Worksheet>';
//            }
//        }
//
//        $xml .= '</Workbook>';
//
//        $this->setData($xml);
//
//        return $this;
    }

    /**
     * Prepare and return XML string for MS Excel XML from array
     *
     * @param array $fields
     * @return string
     */
    protected function _getXmlString(array $fields = array())
    {
        $xmlHeader = '<?xml version="1.0"?>' . "\n";
        $xmlRegexp = '/^<cell><row>(.*)?<\/row><\/cell>\s?$/ms';

        if (is_null($this->_xmlElement)) {
            $xmlString = $xmlHeader . '<cell><row></row></cell>';
            $this->_xmlElement = new SimpleXMLElement($xmlString, LIBXML_NOBLANKS);
        }

        $xmlData = array();
        $xmlData[] = '<Row>';
        foreach ($fields as $value) {
            $this->_xmlElement->row = htmlspecialchars($value);
            $value = str_replace($xmlHeader, '', $this->_xmlElement->asXML());
            $value = preg_replace($xmlRegexp, '\\1', $value);
            if (is_numeric($value)) {
                $value = trim($value);
                $dataType = 'Number';
            } else {
                $dataType = 'String';
            }
            $value = str_replace(array("\r\n", "\r", "\n"), '&#10;', $value);

            $xmlData[] = '<Cell><Data ss:Type="' . $dataType . '">' . $value . '</Data></Cell>';
        }
        $xmlData[] = '</Row>';

        return join('', $xmlData);
    }
}

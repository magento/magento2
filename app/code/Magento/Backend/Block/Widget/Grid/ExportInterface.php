<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block\Widget\Grid;

interface ExportInterface
{
    /**
     * Retrieve grid export types
     *
     * @return array|bool
     */
    public function getExportTypes();

    /**
     * Retrieve grid id
     *
     * @return string
     */
    public function getId();

    /**
     * Render export button
     *
     * @return string
     */
    public function getExportButtonHtml();

    /**
     * Add new export type to grid
     *
     * @param   string $url
     * @param   string $label
     * @return  \Magento\Backend\Block\Widget\Grid
     */
    public function addExportType($url, $label);

    /**
     * Retrieve a file container array by grid data as CSV
     *
     * Return array with keys type and value
     *
     * @return array
     */
    public function getCsvFile();

    /**
     * Retrieve Grid data as CSV
     *
     * @return string
     */
    public function getCsv();

    /**
     * Retrieve data in xml
     *
     * @return string
     */
    public function getXml();

    /**
     * Retrieve a file container array by grid data as MS Excel 2003 XML Document
     *
     * Return array with keys type and value
     *
     * @param string $sheetName
     * @return array
     */
    public function getExcelFile($sheetName = '');

    /**
     * Retrieve grid data as MS Excel 2003 XML Document
     *
     * @return string
     */
    public function getExcel();
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block\Widget\Grid;

/**
 * Interface ExportInterface
 *
 * @api
 * @deprecated in favour of UI component implementation
 */
interface ExportInterface
{
    /**
     * Retrieve grid export types
     *
     * @return array|bool
     * @api
     */
    public function getExportTypes();

    /**
     * Retrieve grid id
     *
     * @return string
     * @api
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
     * @api
     */
    public function getCsvFile();

    /**
     * Retrieve Grid data as CSV
     *
     * @return string
     * @api
     */
    public function getCsv();

    /**
     * Retrieve data in xml
     *
     * @return string
     * @api
     */
    public function getXml();

    /**
     * Retrieve a file container array by grid data as MS Excel 2003 XML Document
     *
     * Return array with keys type and value
     *
     * @param string $sheetName
     * @return array
     * @api
     */
    public function getExcelFile($sheetName = '');

    /**
     * Retrieve grid data as MS Excel 2003 XML Document
     *
     * @return string
     * @api
     */
    public function getExcel();
}

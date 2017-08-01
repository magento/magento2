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
 * @deprecated 2.2.0 in favour of UI component implementation
 * @since 2.0.0
 */
interface ExportInterface
{
    /**
     * Retrieve grid export types
     *
     * @return array|bool
     * @api
     * @since 2.0.0
     */
    public function getExportTypes();

    /**
     * Retrieve grid id
     *
     * @return string
     * @api
     * @since 2.0.0
     */
    public function getId();

    /**
     * Render export button
     *
     * @return string
     * @since 2.0.0
     */
    public function getExportButtonHtml();

    /**
     * Add new export type to grid
     *
     * @param   string $url
     * @param   string $label
     * @return  \Magento\Backend\Block\Widget\Grid
     * @since 2.0.0
     */
    public function addExportType($url, $label);

    /**
     * Retrieve a file container array by grid data as CSV
     *
     * Return array with keys type and value
     *
     * @return array
     * @api
     * @since 2.0.0
     */
    public function getCsvFile();

    /**
     * Retrieve Grid data as CSV
     *
     * @return string
     * @api
     * @since 2.0.0
     */
    public function getCsv();

    /**
     * Retrieve data in xml
     *
     * @return string
     * @api
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getExcelFile($sheetName = '');

    /**
     * Retrieve grid data as MS Excel 2003 XML Document
     *
     * @return string
     * @api
     * @since 2.0.0
     */
    public function getExcel();
}

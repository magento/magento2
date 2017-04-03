<?php

namespace Magento\ImportExport\Model\Import\Source;


/**
 * File parser for import of source format
 *
 */
interface FileParserInterface
{
    /**
     * Must return list of columns
     *
     * @return string[]
     */
    public function getColumnNames();

    /**
     * Reads a single row from a file
     *
     * Must return an array of values in the same order as in getColumnNames call
     *
     * @return array|false
     */
    public function fetchRow();

    /**
     * Rewinds parser to the start of the file
     *
     * @return void
     */
    public function reset();
}

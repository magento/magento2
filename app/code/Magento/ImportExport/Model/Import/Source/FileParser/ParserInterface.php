<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ImportExport\Model\Import\Source\FileParser;

/**
 * File parser for import of source format
 *
 */
interface ParserInterface
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

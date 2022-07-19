<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ImportExport\Model\Import\Source;

use Magento\ImportExport\Model\Import\AbstractSource;

class Base64EncodedCsvData extends AbstractSource
{
    /**
     * @var array
     */
    private $rows;

    /**
     * @var string
     */
    protected $_delimiter = ',';

    /**
     * Field Enclosure character
     *
     * @var string
     */
    protected $_enclosure = '';

    /**
     * Read Data and detect column names
     *
     * @param string $source
     */
    public function __construct(string $source)
    {
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        $source = trim(base64_decode($source));
        $rowsData = preg_split("/\r\n|\n|\r/", $source);
        $colNames = explode(',', $rowsData[0]);
        $this->rows = array_splice($rowsData, 1);
        parent::__construct($colNames);
    }

    /**
     * Read next line from CSV data
     *
     * @return array
     */
    protected function _getNextRow()
    {
        if ($this->_key===count($this->rows)) {
            return [];
        }
        $parsed =str_getcsv($this->rows[$this->_key], ',', '"');
        if (is_array($parsed) && count($parsed) != $this->_colQty) {
            foreach ($parsed as $element) {
                if ($element && strpos($element, "'") !== false) {
                    $this->_foundWrongQuoteFlag = true;
                    break;
                }
            }
        } else {
            $this->_foundWrongQuoteFlag = false;
        }
        return is_array($parsed) ? $parsed : [];
    }
}

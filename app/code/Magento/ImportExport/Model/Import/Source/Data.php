<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ImportExport\Model\Import\Source;

use Magento\ImportExport\Model\Import\AbstractSource;

class Data extends AbstractSource
{
    private $rows;

    /**
     * @var string
     */
    protected $_delimiter = ',';

    /**
     * @var string
     */
    protected $_enclosure = '';

    public function __construct(string $data)
    {
        $rowsData = preg_split("/\r\n|\n|\r/", $data);
        $colNames = explode(',', $rowsData[0]);
        $this->rows = array_splice($rowsData, 1);
        parent::__construct($colNames);
    }

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

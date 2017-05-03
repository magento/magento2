<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ImportExport\Test\Unit\Model\Import\Source\FileParser;

use Magento\ImportExport\Model\Import\Source\FileParser\ParserInterface;

class FakeParser implements ParserInterface
{
    private $rows;
    private $columns;
    private $position;

    public function __construct(array $columns = [], array $rows = [])
    {
        $this->columns = $columns;
        $this->rows = $rows;
        $this->position = 0;
    }


    public function getColumnNames()
    {
        return $this->columns;
    }

    public function fetchRow()
    {
        if ($this->isEndOfRows()) {
            return false;
        }

        return $this->rows[$this->position++];
    }

    public function reset()
    {
        $this->position = 0;
    }

    private function isEndOfRows()
    {
        return !isset($this->rows[$this->position]);
    }
}

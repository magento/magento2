<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\OfflineShipping\Model\ResourceModel\Carrier\Tablerate\CSV;

class ColumnResolver
{
    const COLUMN_COUNTRY = 'Country';
    const COLUMN_REGION = 'Region/State';
    const COLUMN_ZIP = 'Zip/Postal Code';
    const COLUMN_WEIGHT = 'Weight (and above)';
    const COLUMN_WEIGHT_DESTINATION = 'Weight (and above)';
    const COLUMN_PRICE = 'Shipping Price';

    /**
     * @var array
     */
    private $nameToPositionIdMap = [
        self::COLUMN_COUNTRY => 0,
        self::COLUMN_REGION => 1,
        self::COLUMN_ZIP => 2,
        self::COLUMN_WEIGHT => 3,
        self::COLUMN_WEIGHT_DESTINATION => 3,
        self::COLUMN_PRICE => 4,
    ];

    /**
     * @var array
     */
    private $headers;

    /**
     * ColumnResolver constructor.
     * @param array $headers
     * @param array $columns
     */
    public function __construct(array $headers, array $columns = [])
    {
        $this->nameToPositionIdMap = array_merge($this->nameToPositionIdMap, $columns);
        $this->headers = array_map('trim', $headers);
    }

    /**
     * @param string $column
     * @param array $values
     * @return string|int|float|null
     * @throws ColumnNotFoundException
     */
    public function getColumnValue($column, array $values)
    {
        $column = (string) $column;
        $columnIndex = array_search($column, $this->headers, true);
        if (false === $columnIndex) {
            if (array_key_exists($column, $this->nameToPositionIdMap)) {
                $columnIndex = $this->nameToPositionIdMap[$column];
            } else {
                throw new ColumnNotFoundException(__('Requested column "%1" cannot be resolved', $column));
            }
        }

        if (!array_key_exists($columnIndex, $values)) {
            throw new ColumnNotFoundException(__('Column "%1" not found', $column));
        }

        return  trim($values[$columnIndex]);
    }
}

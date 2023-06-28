<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\OfflineShipping\Model\ResourceModel\Carrier\Tablerate\CSV;

class ColumnResolver
{
    public const COLUMN_COUNTRY = 'Country';
    public const COLUMN_REGION = 'Region/State';
    public const COLUMN_ZIP = 'Zip/Postal Code';
    public const COLUMN_WEIGHT = 'Weight (and above)';
    public const COLUMN_WEIGHT_DESTINATION = 'Weight (and above)';
    public const COLUMN_PRICE = 'Shipping Price';

    /**
     * @var array
     */
    private $nameToPositionIdMap = [
        self::COLUMN_COUNTRY => 0,
        self::COLUMN_REGION => 1,
        self::COLUMN_ZIP => 2,
        self::COLUMN_WEIGHT => 3, // @phpstan-ignore-line
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
     * Method to get column value.
     *
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

        return  trim($values[$columnIndex] ?? '');
    }
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Adapter\Mysql\Aggregation;

use Magento\Framework\DB\Select;
use Magento\Framework\Search\Dynamic\IntervalInterface;

/**
 * MySQL search aggregation interval.
 *
 * @deprecated
 * @see \Magento\ElasticSearch
 */
class Interval implements IntervalInterface
{
    /**
     * Minimal possible value
     */
    const DELTA = 0.005;

    /**
     * @var Select
     */
    private $select;

    /**
     * @param Select $select
     */
    public function __construct(Select $select)
    {
        $this->select = $select;
    }

    /**
     * Get value field
     *
     * @return string
     */
    private function getValueFiled()
    {
        $field = $this->select->getPart(Select::COLUMNS)[0];

        return $field[1];
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function load($limit, $offset = null, $lower = null, $upper = null)
    {
        $select = clone $this->select;
        $value = $this->getValueFiled();
        if ($lower !== null) {
            $select->where("${value} >= ?", $lower - self::DELTA);
        }
        if ($upper !== null) {
            $select->where("${value} < ?", $upper - self::DELTA);
        }
        $select->order("value ASC")
            ->limit($limit, $offset);

        return $this->arrayValuesToFloat(
            $this->select->getConnection()
                ->fetchCol($select)
        );
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function loadPrevious($data, $index, $lower = null)
    {
        $select = clone $this->select;
        $value = $this->getValueFiled();
        $select->columns(['count' => 'COUNT(*)'])
            ->where("${value} <  ?", $data - self::DELTA);
        if ($lower !== null) {
            $select->where("${value} >= ?", $lower - self::DELTA);
        }
        $offset = $this->select->getConnection()
            ->fetchRow($select)['count'];
        if (!$offset) {
            return false;
        }

        return $this->load($index - $offset + 1, $offset - 1, $lower);
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function loadNext($data, $rightIndex, $upper = null)
    {
        $select = clone $this->select;
        $value = $this->getValueFiled();
        $select->columns(['count' => 'COUNT(*)'])
            ->where("${value} > ?", $data + self::DELTA);

        if ($upper !== null) {
            $select->where("${value} < ? ", $data - self::DELTA);
        }

        $offset = $this->select->getConnection()
            ->fetchRow($select)['count'];

        if (!$offset) {
            return false;
        }

        $select = clone $this->select;
        $select->where("${value} >= ?", $data - self::DELTA);
        if ($upper !== null) {
            $select->where("${value} < ? ", $data - self::DELTA);
        }
        $select->order("${value} DESC")
            ->limit($rightIndex - $offset + 1, $offset - 1);

        return $this->arrayValuesToFloat(
            array_reverse(
                $this->select->getConnection()
                    ->fetchCol($select)
            )
        );
    }

    /**
     * Convert array values to float.
     *
     * @param array $prices
     * @return array
     */
    private function arrayValuesToFloat($prices)
    {
        $returnPrices = [];
        if (is_array($prices) && !empty($prices)) {
            $returnPrices = array_map('floatval', $prices);
        }

        return $returnPrices;
    }
}

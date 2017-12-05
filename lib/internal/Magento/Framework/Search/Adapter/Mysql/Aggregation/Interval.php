<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Search\Adapter\Mysql\Aggregation;

use Magento\Framework\DB\Select;
use Magento\Framework\Search\Dynamic\IntervalInterface;

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

        return (string) $field[1];
    }

    /**
     * Get value alias
     *
     * @return string
     */
    private function getValueAlias()
    {
        $field = $this->select->getPart(Select::COLUMNS)[0];

        return $field[2];
    }

    /**
     * {@inheritdoc}
     */
    public function load($limit, $offset = null, $lower = null, $upper = null)
    {
        $select = clone $this->select;
        $valueFiled = $this->getValueFiled();
        $valueAlias = $this->getValueAlias();
        if ($lower !== null) {
            $select->where($valueFiled . ' >= ?', $lower - self::DELTA);
        }
        if ($upper !== null) {
            $select->where($valueFiled . ' < ?', $upper - self::DELTA);
        }
        $select->order($valueAlias . ' ASC')
            ->limit($limit, $offset);

        return $this->arrayValuesToFloat(
            $this->select->getConnection()
                ->fetchCol($select)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function loadPrevious($data, $index, $lower = null)
    {
        $select = clone $this->select;
        $valueFiled = $this->getValueFiled();
        $select->columns(['count' => 'COUNT(*)'])
            ->where($valueFiled . ' <  ?', $data - self::DELTA);
        if ($lower !== null) {
            $select->where($valueFiled . ' >= ?', $lower - self::DELTA);
        }
        $offset = $this->select->getConnection()
            ->fetchRow($select)['count'];
        if (!$offset) {
            return false;
        }

        return $this->load($index - $offset + 1, $offset - 1, $lower);
    }

    /**
     * {@inheritdoc}
     */
    public function loadNext($data, $rightIndex, $upper = null)
    {
        $select = clone $this->select;
        $valueFiled = $this->getValueFiled();
        $valueAlias = $this->getValueAlias();
        $select->columns(['count' => 'COUNT(*)'])
            ->where($valueFiled . ' > ?', $data + self::DELTA);

        if ($upper !== null) {
            $select->where($valueFiled . ' < ?', $data - self::DELTA);
        }

        $offset = $this->select->getConnection()
            ->fetchRow($select)['count'];

        if (!$offset) {
            return false;
        }

        $select = clone $this->select;
        $select->where($valueFiled . ' >= ?', $data - self::DELTA);
        if ($upper !== null) {
            $select->where($valueFiled . ' < ?', $data - self::DELTA);
        }
        $select->order($valueAlias . ' DESC')
            ->limit($rightIndex - $offset + 1, $offset - 1);

        return $this->arrayValuesToFloat(
            array_reverse(
                $this->select->getConnection()
                    ->fetchCol($select)
            )
        );
    }

    /**
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

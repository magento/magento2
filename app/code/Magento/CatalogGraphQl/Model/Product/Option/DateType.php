<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogGraphQl\Model\Product\Option;

use Magento\Catalog\Model\Product\Option\Type\Date as ProductDateOptionType;
use Magento\Framework\Stdlib\DateTime;

/**
 * Catalog product option date validator
 */
class DateType extends ProductDateOptionType
{
    /**
     * {@inheritdoc}
     */
    public function validateUserValue($values)
    {
        if ($this->_dateExists() || $this->_timeExists()) {
            return parent::validateUserValue($this->formatValues($values));
        }

        return $this;
    }

    /**
     * @param array $values
     * @return array mixed
     */
    protected function formatValues($values)
    {
        if (isset($values[$this->getOption()->getId()])) {
            $value = $values[$this->getOption()->getId()];
            $dateTime = \DateTime::createFromFormat(DateTime::DATETIME_PHP_FORMAT, $value);
            $values[$this->getOption()->getId()] = [
                'date' => $value,
                'year' => $dateTime->format('Y'),
                'month' => $dateTime->format('m'),
                'day' => $dateTime->format('d'),
                'hour' => $dateTime->format('H'),
                'minute' => $dateTime->format('i'),
                'day_part' => $dateTime->format('a'),
            ];
        }

        return $values;
    }

    /**
     * @return bool
     */
    public function useCalendar()
    {
        return false;
    }
}

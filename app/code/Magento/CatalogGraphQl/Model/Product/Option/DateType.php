<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Product\Option;

use Magento\Catalog\Model\Product\Option\Type\Date as ProductDateOptionType;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\DateTime;

/**
 * @inheritdoc
 */
class DateType extends ProductDateOptionType
{
    /**
     * Make valid string as a value of date option type for GraphQl queries
     *
     * @param array $values All product option values, i.e. array (option_id => mixed, option_id => mixed...)
     * @return ProductDateOptionType
     */
    public function validateUserValue($values)
    {
        if ($this->_dateExists() || $this->_timeExists()) {
            return parent::validateUserValue($this->formatValues($values));
        }

        return $this;
    }

    /**
     * Format date value from string to date array
     *
     * @param [] $values
     * @return []
     * @throws LocalizedException
     */
    private function formatValues($values)
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
     * @inheritdoc
     */
    public function useCalendar()
    {
        return false;
    }
}

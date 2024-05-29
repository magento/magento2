<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Plugin\Model\ResourceModel\Order;

use DateTime;
use DateTimeInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;

class OrderGridCollectionFilter
{
    /**
     * @var TimezoneInterface
     */
    private TimezoneInterface $timeZone;

    /**
     * Timezone converter interface
     *
     * @param TimezoneInterface $timeZone
     */
    public function __construct(
        TimezoneInterface $timeZone
    ) {
        $this->timeZone = $timeZone;
    }

    /**
     * Conditional column filters with timezone convertor interface
     *
     * @param  SearchResult $subject
     * @param  \Closure     $proceed
     * @param  string       $field
     * @param  string|null  $condition
     * @return SearchResult|mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function aroundAddFieldToFilter(
        SearchResult $subject,
        \Closure $proceed,
        $field,
        $condition = null
    ) {
        if ($field === 'created_at' || $field === 'order_created_at') {
            if (is_array($condition)) {
                foreach ($condition as $key => $value) {
                    if ($value = $this->isValidDate($value)) {
                        $condition[$key] = $value->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d H:i:s');
                    }
                }
            }

            $fieldName = $subject->getConnection()->quoteIdentifier($field);
            $condition = $subject->getConnection()->prepareSqlCondition($fieldName, $condition);
            $subject->getSelect()->where($condition, null, Select::TYPE_CONDITION);

            return $subject;
        }

        return $proceed($field, $condition);
    }

    /**
     * Validate date string
     *
     * @param mixed $datetime
     * @return mixed
     */
    private function isValidDate(mixed $datetime): mixed
    {
        try {
            return $datetime instanceof DateTimeInterface
                ? $datetime : (is_string($datetime)
                    ? new DateTime($datetime, new \DateTimeZone($this->timeZone->getConfigTimezone())) : false);
        } catch (\Exception $e) {
            return false;
        }
    }
}

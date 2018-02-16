<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cron\Model\ResourceModel\Schedule\Expression\Part\NumericParser;

/**
 * Cron expression part numeric
 *
 * @api
 */
class Generic implements NumericInterface
{
    /**
     * @var int
     */
    private $rangeMin = 0;

    /**
     * @var int
     */
    private $rangeMax = PHP_INT_MAX;

    /**
     * @var array
     */
    private $valuesMap = [
        'jan' => 1,
        'feb' => 2,
        'mar' => 3,
        'apr' => 4,
        'may' => 5,
        'jun' => 6,
        'jul' => 7,
        'aug' => 8,
        'sep' => 9,
        'oct' => 10,
        'nov' => 11,
        'dec' => 12,
        'sun' => 0,
        'mon' => 1,
        'tue' => 2,
        'wed' => 3,
        'thu' => 4,
        'fri' => 5,
        'sat' => 6,
    ];

    /**
     * Generic constructor.
     *
     * @param int   $rangeMin
     * @param int   $rangeMax
     * @param array $valuesMap
     */
    public function __construct(
        $rangeMin,
        $rangeMax,
        $valuesMap
    ) {
        $this->rangeMin = isset($rangeMin) ? $rangeMin : $this->rangeMin;
        $this->rangeMax = isset($rangeMax) ? $rangeMax : $this->rangeMax;
        $this->valuesMap = isset($valuesMap) ? $valuesMap : $this->valuesMap;
    }

    /**
     * @return int
     */
    public function getRangeMin()
    {
        return $this->rangeMin;
    }

    /**
     * @return int
     */
    public function getRangeMax()
    {
        return $this->rangeMax;
    }

    /**
     * Get cron expression part number from value
     *
     * @param int|string $value
     *
     * @return int|bool
     */
    public function getNumber($value)
    {
        if (is_string($value)) {
            $numberKey = strtolower(substr($value, 0, 3));
            if (isset($this->valuesMap[$numberKey])) {
                $value = $this->valuesMap[$numberKey];
            }
        }

        if (!preg_match('/^\d+$/', $value) || $value > $this->getRangeMax() || $value < $this->getRangeMin()) {
            return false;
        }

        return (int)$value;
    }
}

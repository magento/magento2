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
class WeekDay extends Generic implements NumericInterface
{
    /**
     * @var int
     */
    private $rangeMin = 0;

    /**
     * @var int
     */
    private $rangeMax = 6;

    /**
     * @var array
     */
    private $valuesMap = [
        'sun' => 0,
        'mon' => 1,
        'tue' => 2,
        'wed' => 3,
        'thu' => 4,
        'fri' => 5,
        'sat' => 6,
    ];

    /**
     * WeekDay constructor.
     */
    public function __construct()
    {
        parent::__construct($this->rangeMin, $this->rangeMax, $this->valuesMap);
    }
}

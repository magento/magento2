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
class Month extends Generic
{
    /**
     * @var int
     */
    private $rangeMin = 1;

    /**
     * @var int
     */
    private $rangeMax = 12;

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
    ];

    /**
     * Month constructor.
     */
    public function __construct()
    {
        parent::__construct($this->rangeMin, $this->rangeMax, $this->valuesMap);
    }
}

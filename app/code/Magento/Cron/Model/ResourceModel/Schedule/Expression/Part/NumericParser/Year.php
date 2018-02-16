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
class Year extends Generic
{
    /**
     * @var int
     */
    private $rangeMin = 1970;

    /**
     * @var int
     */
    private $rangeMax = 2099;

    /**
     * @var array
     */
    private $valuesMap = [];

    /**
     * Year constructor.
     */
    public function __construct()
    {
        parent::__construct($this->rangeMin, $this->rangeMax, $this->valuesMap);
    }
}

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
class Minutes extends Generic
{
    /**
     * @var int
     */
    private $rangeMin = 0;

    /**
     * @var int
     */
    private $rangeMax = 59;

    /**
     * @var array
     */
    private $valuesMap = [];

    /**
     * Minutes constructor.
     */
    public function __construct()
    {
        parent::__construct($this->rangeMin, $this->rangeMax, $this->valuesMap);
    }
}

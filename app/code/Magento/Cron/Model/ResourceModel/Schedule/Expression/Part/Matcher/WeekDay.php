<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cron\Model\ResourceModel\Schedule\Expression\Part\Matcher;

/**
 * Cron expression part matcher
 *
 * @api
 */
class WeekDay extends Generic implements MatcherInterface
{
    /**
     * @var string
     */
    private $dateExpr = '%w';

    /**
     * WeekDay constructor.
     */
    public function __construct()
    {
        parent::__construct($this->dateExpr);
    }
}

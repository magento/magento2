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
class Generic implements MatcherInterface
{
    /**
     * @var string
     */
    private $dateExpr;

    /**
     * Generic constructor.
     *
     * @param string $dateExpr
     */
    public function __construct(
        $dateExpr
    ) {
        $this->dateExpr = $dateExpr;
    }

    /**
     * Get number from timestamp
     *
     * @param int|string $timestamp
     *
     * @return int
     */
    public function getNumber($timestamp)
    {
        return (int)strftime($this->dateExpr, $timestamp);
    }
}

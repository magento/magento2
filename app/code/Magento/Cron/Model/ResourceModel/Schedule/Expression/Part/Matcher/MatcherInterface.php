<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cron\Model\ResourceModel\Schedule\Expression\Part\Matcher;

/**
 * Cron expression part matcher interface
 *
 * @api
 */
interface MatcherInterface
{
    /**
     * Get number from timestamp
     *
     * @param int|string $timestamp
     *
     * @return int
     */
    public function getNumber($timestamp);
}

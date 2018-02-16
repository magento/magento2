<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cron\Model\ResourceModel\Schedule\Expression\Part;

use Magento\Cron\Model\ResourceModel\Schedule\Expression\PartInterface;

/**
 * Cron expression part matcher interface
 *
 * @api
 */
interface MatcherInterface
{
    /**
     * Perform match of cron expression part against number
     *
     * @param PartInterface $part
     * @param int           $number
     *
     * @return bool
     */
    public function match(PartInterface $part, $number);
}

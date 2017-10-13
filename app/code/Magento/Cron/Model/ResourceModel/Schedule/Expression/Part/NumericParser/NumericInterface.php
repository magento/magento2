<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cron\Model\ResourceModel\Schedule\Expression\Part\NumericParser;

/**
 * Cron expression part numeric interface
 *
 * @api
 */
interface NumericInterface
{
    /**
     * @return int
     */
    public function getRangeMin();

    /**
     * @return int
     */
    public function getRangeMax();

    /**
     * Get cron expression part number from value
     *
     * @param int|string $value
     *
     * @return int|bool
     */
    public function getNumber($value);
}

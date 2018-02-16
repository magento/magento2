<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cron\Model\ResourceModel\Schedule\Expression;

use Magento\Framework\Exception\CronException;

/**
 * Cron expression part encapsulation interface
 *
 * @api
 */
interface PartInterface
{
    /**
     * @return string[]
     */
    public function getValidatorHandlers();

    /**
     * Numeric parser for expression part
     *
     * @return string
     */
    public function getNumericParser();

    /**
     * @return string
     */
    public function getPartMatcher();

    /**
     * Set part value
     *
     * @param string $partValue
     *
     * @throws CronException
     * @return void
     */
    public function setPartValue($partValue);

    /**
     * Get cron expression part string value
     *
     * @return string
     */
    public function getPartValue();

    /**
     * Get cron expression part is valid
     *
     * @return bool
     */
    public function validate();

    /**
     * Reset part inner data
     *
     * @return void
     */
    public function reset();

    /**
     * Get cron expression part matches number
     *
     * @param int $number
     *
     * @return bool
     */
    public function match($number);
}

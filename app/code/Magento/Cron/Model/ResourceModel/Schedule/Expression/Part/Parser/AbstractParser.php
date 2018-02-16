<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cron\Model\ResourceModel\Schedule\Expression\Part\Parser;

/**
 * Cron expression part parser class
 *
 * @api
 */
abstract class AbstractParser implements ParserInterface
{
    /**
     * @var string
     */
    private $explodeChar;

    public function __construct(
        $explodeChar
    ) {
        $this->explodeChar = $explodeChar;
    }

    /**
     * Perform parse of cron expression part
     *
     * @param string $partValue
     *
     * @return bool|array
     */
    public function parse($partValue)
    {
        $partValue = trim($partValue, $this->explodeChar . ' ');
        return !strlen($partValue) ? [] : explode($this->explodeChar, $partValue);
    }
}

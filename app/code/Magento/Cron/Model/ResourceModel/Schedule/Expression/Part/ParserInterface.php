<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cron\Model\ResourceModel\Schedule\Expression\Part;

/**
 * Cron expression part parser interface
 *
 * @api
 */
interface ParserInterface
{
    /**
     * Perform parse of cron expression part
     *
     * @param string $partValue
     * @param string $parserType
     *
     * @return bool|array
     */
    public function parse($partValue, $parserType);
}

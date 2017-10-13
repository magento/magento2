<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cron\Model\ResourceModel\Schedule\Expression\Part;

use Magento\Cron\Model\ResourceModel\Schedule\Expression\PartInterface;

/**
 * Cron expression part validator interface
 *
 * @api
 */
interface ValidatorInterface
{
    /**
     * Perform validation of cron expression part
     *
     * @param PartInterface $part
     *
     * @return bool
     */
    public function validate(PartInterface $part);
}

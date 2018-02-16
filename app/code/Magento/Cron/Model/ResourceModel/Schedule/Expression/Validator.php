<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cron\Model\ResourceModel\Schedule\Expression;

use Magento\Cron\Model\ResourceModel\Schedule\ExpressionInterface;

/**
 * Schedule cron expression validator
 *
 * @api
 */
class Validator implements ValidatorInterface
{
    const MIN_PARTS_NUMBER = 5;
    const MAX_PARTS_NUMBER = 6;

    /**
     * Perform validation of cron expression
     *
     * @param ExpressionInterface $expression
     *
     * @return bool
     */
    public function validate(ExpressionInterface $expression)
    {
        $parts = $expression->getParts();

        if (empty($parts)) {
            return false;
        }

        if (count($parts) < self::MIN_PARTS_NUMBER || count($parts) > self::MAX_PARTS_NUMBER) {
            return false;
        }

        /** @var PartInterface $part */
        foreach ($parts as $part) {
            if (!$part instanceof PartInterface || !$part->validate()) {
                return false;
            }
        }

        return true;
    }
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cron\Model\ResourceModel\Schedule\Expression;

use Magento\Cron\Model\ResourceModel\Schedule\Expression\Part\MatcherFactory;
use Magento\Cron\Model\ResourceModel\Schedule\ExpressionInterface;

/**
 * Cron expression matcher
 *
 * @api
 */
class Matcher implements MatcherInterface
{
    /**
     * @var MatcherFactory
     */
    private $matcherFactory;

    /**
     * Matcher constructor.
     *
     * @param MatcherFactory $matcherFactory
     */
    public function __construct(
        MatcherFactory $matcherFactory
    ) {
        $this->matcherFactory = $matcherFactory;
    }

    /**
     * Perform match of cron expression against timestamp
     *
     * @param ExpressionInterface $expression
     * @param int                 $timestamp
     *
     * @return bool
     */
    public function match(ExpressionInterface $expression, $timestamp)
    {
        if (empty($expression->getParts()) || !$expression->validate()) {
            return false;
        }

        $parts = $expression->getParts();

        /** @var PartInterface $part */
        foreach ($parts as $part) {
            $number = $this->matcherFactory->create($part->getPartMatcher())->getNumber($timestamp);
            if (!$part->match($number)) {
                return false;
            }
        }

        return true;
    }
}

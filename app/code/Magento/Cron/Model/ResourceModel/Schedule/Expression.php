<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cron\Model\ResourceModel\Schedule;

use Magento\Cron\Model\ResourceModel\Schedule\Expression\MatcherInterface;
use Magento\Cron\Model\ResourceModel\Schedule\Expression\ParserInterface;
use Magento\Cron\Model\ResourceModel\Schedule\Expression\PartInterface;
use Magento\Cron\Model\ResourceModel\Schedule\Expression\ValidatorInterface;
use Magento\Framework\Exception\CronException;

/**
 * Cron expression encapsulation class
 *
 * @api
 */
class Expression implements ExpressionInterface
{
    /**
     * @var ValidatorInterface
     */
    private $expressionValidator;

    /**
     * @var ParserInterface
     */
    private $expressionParser;

    /**
     * @var MatcherInterface
     */
    private $expressionMatcher;

    /**
     * @var string
     */
    private $cronExpr;

    /**
     * @var PartInterface[]
     */
    private $parts;

    /**
     * @var bool
     */
    private $isValid;

    /**
     * @param ValidatorInterface $expressionValidator
     * @param ParserInterface    $expressionParser
     * @param MatcherInterface   $expressionMatcher
     */
    public function __construct(
        ValidatorInterface $expressionValidator,
        ParserInterface $expressionParser,
        MatcherInterface $expressionMatcher
    ) {
        $this->expressionValidator = $expressionValidator;
        $this->expressionParser = $expressionParser;
        $this->expressionMatcher = $expressionMatcher;
    }

    /**
     * Set cron expression
     *
     * @param string $cronExpr
     *
     * @throws CronException
     * @return void
     */
    public function setCronExpr($cronExpr)
    {
        $this->reset();
        $this->cronExpr = $cronExpr;

        if (empty($this->getParts()) || !$this->validate()) {
            throw new CronException(__('Invalid cron expression: %1', $cronExpr));
        }
    }

    /**
     * Get cron expression
     *
     * @return string
     */
    public function getCronExpr()
    {
        return isset($this->cronExpr) ? (string)$this->cronExpr : '';
    }

    /**
     * Get cron expression is valid
     *
     * @return bool
     */
    public function validate()
    {
        if (!isset($this->isValid)) {
            $this->isValid = $this->expressionValidator->validate($this);
        }
        return $this->isValid;
    }

    /**
     * Get cron expression parts array
     *
     * @return bool|PartInterface[]
     */
    public function getParts()
    {
        if (!isset($this->parts)) {
            $this->parts = $this->expressionParser->parse($this);
        }
        return $this->parts;
    }

    /**
     * Match cron expression against timestamp
     *
     * @param int $timestamp
     *
     * @return bool
     */
    public function match($timestamp)
    {
        return $this->expressionMatcher->match($this, $timestamp);
    }

    /**
     * Reset expression inner data
     *
     * @return void
     */
    public function reset()
    {
        $this->cronExpr = null;
        $this->parts = null;
        $this->isValid = null;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getCronExpr();
    }
}

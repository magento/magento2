<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cron\Model\ResourceModel\Schedule\Expression;

use Magento\Cron\Model\ResourceModel\Schedule\Expression\Part\MatcherFactory;
use Magento\Cron\Model\ResourceModel\Schedule\Expression\Part\MatcherInterface as ExpressionPartMatcherInterface;
use Magento\Cron\Model\ResourceModel\Schedule\Expression\Part\NumericParserFactory;
use Magento\Cron\Model\ResourceModel\Schedule\Expression\Part\ValidatorInterface as ExpressionPartValidatorInterface;
use Magento\Framework\Exception\CronException;

/**
 * Cron expression part encapsulation class
 *
 * @api
 */
class Part implements PartInterface
{
    /**
     * @var ExpressionPartValidatorInterface
     */
    private $validator;

    /**
     * @var ExpressionPartMatcherInterface
     */
    private $matcher;

    /**
     * @var string
     */
    private $partValue;

    /**
     * @var bool
     */
    private $isValid;

    /**
     * @var string[]
     */
    private $validatorHandlers = [];

    /**
     * @var string
     */
    private $numericParser = NumericParserFactory::GENERIC_NUMERIC;

    /**
     * @var string
     */
    private $partMatcher = MatcherFactory::GENERIC_MATCHER;

    /**
     * @return string[]
     */
    public function getValidatorHandlers()
    {
        return $this->validatorHandlers;
    }

    /**
     * Numeric parser for expression part
     *
     * @return string
     */
    public function getNumericParser()
    {
        return $this->numericParser;
    }

    /**
     * @return string
     */
    public function getPartMatcher()
    {
        return $this->partMatcher;
    }

    /**
     * Part constructor.
     *
     * @param ExpressionPartValidatorInterface $validator
     * @param ExpressionPartMatcherInterface   $matcher
     * @param string[]                         $validatorHandlers
     * @param string                           $numericParser
     * @param string                           $partMatcher
     */
    public function __construct(
        ExpressionPartValidatorInterface $validator,
        ExpressionPartMatcherInterface $matcher,
        $validatorHandlers = null,
        $numericParser = null,
        $partMatcher = null
    ) {
        $this->validator = $validator;
        $this->matcher = $matcher;

        $this->validatorHandlers = isset($validatorHandlers) ? $validatorHandlers : $this->validatorHandlers;
        $this->numericParser = isset($numericParser) ? $numericParser : $this->numericParser;
        $this->partMatcher = isset($partMatcher) ? $partMatcher : $this->partMatcher;
    }

    /**
     * Set part value
     *
     * @param string $partValue
     *
     * @throws CronException
     * @return void
     */
    public function setPartValue($partValue)
    {
        $this->reset();
        $this->partValue = $partValue;
    }

    /**
     * Get cron expression part string value
     *
     * @return string
     */
    public function getPartValue()
    {
        return isset($this->partValue) ? (string)$this->partValue : '';
    }

    /**
     * Get cron expression part is valid
     *
     * @return bool
     */
    public function validate()
    {
        if (!isset($this->isValid)) {
            $this->isValid = $this->validator->validate($this);
        }
        return $this->isValid;
    }

    /**
     * Get cron expression part matches number
     *
     * @param int $number
     *
     * @return bool
     */
    public function match($number)
    {
        return $this->matcher->match($this, $number);
    }

    /**
     * Reset part inner data
     *
     * @return void
     */
    public function reset()
    {
        $this->partValue = null;
        $this->isValid = null;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getPartValue();
    }
}

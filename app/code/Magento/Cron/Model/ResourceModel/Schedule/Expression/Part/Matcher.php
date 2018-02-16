<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cron\Model\ResourceModel\Schedule\Expression\Part;

use Magento\Cron\Model\ResourceModel\Schedule\Expression\PartInterface;

/**
 * Cron expression part matcher
 *
 * @api
 */
class Matcher implements MatcherInterface
{
    /**
     * @var ParserInterface
     */
    private $parser;

    /**
     * @var MatcherFactory
     */
    private $matcherFactory;

    /**
     * @var NumericParserFactory
     */
    private $numericFactory;

    /**
     * @var ValidatorHandlerFactory
     */
    private $validatorHandlerFactory;

    /**
     * Matcher constructor.
     *
     * @param ParserInterface         $parser
     * @param MatcherFactory          $matcherFactory
     * @param NumericParserFactory    $numericFactory
     * @param ValidatorHandlerFactory $validatorHandlerFactory
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function __construct(
        ParserInterface $parser,
        MatcherFactory $matcherFactory,
        NumericParserFactory $numericFactory,
        ValidatorHandlerFactory $validatorHandlerFactory
    ) {
        $this->parser = $parser;
        $this->matcherFactory = $matcherFactory;
        $this->numericFactory = $numericFactory;
        $this->validatorHandlerFactory = $validatorHandlerFactory;
    }

    /**
     * Perform match of cron expression part against timestamp
     *
     * @param PartInterface $part
     * @param int           $number
     *
     * @return bool
     */
    public function match(PartInterface $part, $number)
    {
        if (!$part->validate()) {
            return false;
        }

        $match = false;
        foreach ($this->parser->parse($part->getPartValue(), ParserFactory::LIST_PARSER) as $subPartValue) {
            if ($this->matchCronExpressionPart($part, $subPartValue, $number)) {
                $match = true;
                break;
            }
        }

        return $match;
    }

    /**
     * @param PartInterface $part
     * @param string        $cronExpr
     * @param int           $num
     *
     * @return bool
     */
    private function matchCronExpressionPart(PartInterface $part, $cronExpr, $num)
    {
        // handle ALL match
        if ($this->handleAllMatch($part, $cronExpr)) {
            return true;
        }

        // handle modulus
        list($cronExpr, $mod) = $this->handleModulus($cronExpr);
        // handle all match by modulus
        list($fromValue, $toValue) = $this->handleAllMatchByModulus($part, $cronExpr);

        // handle range
        $rangeParts = $this->parser->parse($cronExpr, ParserFactory::RANGE_PARSER);
        if (!isset($fromValue) && count($rangeParts) > 1) {
            $fromValue = $this->numericFactory->create($part->getNumericParser())->getNumber($rangeParts[0]);
            $toValue = $this->numericFactory->create($part->getNumericParser())->getNumber($rangeParts[1]);
        }

        // handle regular token
        if (!isset($fromValue)) {
            $fromValue = $this->numericFactory->create($part->getNumericParser())->getNumber($cronExpr);
            $toValue = $fromValue;
        }

        return $num >= $fromValue && $num <= $toValue && $num % $mod === 0;
    }

    /**
     * @param PartInterface $part
     * @param               $cronExpr
     *
     * @return bool
     */
    private function handleAllMatch(PartInterface $part, $cronExpr)
    {
        if ($this->validatorHandlerFactory->create(ValidatorHandlerFactory::ASTERISK_VALIDATION_HANDLER)
                ->handle($part, $cronExpr) === true
        ) {
            return true;
        }

        if ($this->validatorHandlerFactory->create(ValidatorHandlerFactory::QUESTION_MARK_VALIDATION_HANDLER)
                ->handle($part, $cronExpr) === true
        ) {
            return true;
        }

        return false;
    }

    /**
     * @param $cronExpr
     *
     * @return array
     */
    private function handleModulus($cronExpr)
    {
        $mod = 1;
        $modulusParts = $this->parser->parse($cronExpr, ParserFactory::MODULUS_PARSER);
        if (count($modulusParts) > 1) {
            $cronExpr = $modulusParts[0];
            $mod = $modulusParts[1];
        }

        return [$cronExpr, $mod];
    }

    /**
     * @param PartInterface $part
     * @param               $cronExpr
     *
     * @return array
     */
    private function handleAllMatchByModulus(PartInterface $part, $cronExpr)
    {
        $fromValue = null;
        $toValue = null;

        if ($this->validatorHandlerFactory->create(ValidatorHandlerFactory::ASTERISK_VALIDATION_HANDLER)
                ->handle($part, $cronExpr) === true
        ) {
            $fromValue = $this->numericFactory->create($part->getNumericParser())->getRangeMin();
            $toValue = $this->numericFactory->create($part->getNumericParser())->getRangeMax();
        }

        return [$fromValue, $toValue];
    }
}

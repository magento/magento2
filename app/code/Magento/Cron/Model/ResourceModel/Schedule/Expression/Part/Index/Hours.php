<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cron\Model\ResourceModel\Schedule\Expression\Part\Index;

use Magento\Cron\Model\ResourceModel\Schedule\Expression\Part;
use Magento\Cron\Model\ResourceModel\Schedule\Expression\Part\Matcher as ExpressionPartMatcher;
use Magento\Cron\Model\ResourceModel\Schedule\Expression\Part\MatcherFactory;
use Magento\Cron\Model\ResourceModel\Schedule\Expression\Part\NumericParserFactory;
use Magento\Cron\Model\ResourceModel\Schedule\Expression\Part\Validator as ExpressionPartValidator;
use Magento\Cron\Model\ResourceModel\Schedule\Expression\Part\ValidatorHandlerFactory;
use Magento\Cron\Model\ResourceModel\Schedule\Expression\PartInterface;

/**
 * Cron expression part index class
 *
 * @api
 */
class Hours extends Part implements PartInterface
{
    /**
     * @var string[]
     */
    private $validatorHandlers = [
        ValidatorHandlerFactory::ASTERISK_VALIDATION_HANDLER,
        ValidatorHandlerFactory::MODULUS_VALIDATION_HANDLER,
        ValidatorHandlerFactory::ASTERISK_MODULUS_VALIDATION_HANDLER,
        ValidatorHandlerFactory::RANGE_VALIDATION_HANDLER,
        ValidatorHandlerFactory::REGULAR_VALIDATION_HANDLER,
    ];

    /**
     * @var string
     */
    private $numericParser = NumericParserFactory::HOURS_NUMERIC;

    /**
     * @var string
     */
    private $partMatcher = MatcherFactory::HOURS_MATCHER;

    /**
     * Hours constructor.
     *
     * @param ExpressionPartValidator $validator
     * @param ExpressionPartMatcher   $matcher
     */
    public function __construct(
        ExpressionPartValidator $validator,
        ExpressionPartMatcher $matcher
    ) {
        parent::__construct(
            $validator,
            $matcher,
            $this->validatorHandlers,
            $this->numericParser,
            $this->partMatcher
        );
    }
}

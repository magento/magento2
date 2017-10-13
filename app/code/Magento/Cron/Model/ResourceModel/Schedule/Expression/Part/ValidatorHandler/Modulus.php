<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cron\Model\ResourceModel\Schedule\Expression\Part\ValidatorHandler;

use Magento\Cron\Model\ResourceModel\Schedule\Expression\Part\NumericParserFactory;
use Magento\Cron\Model\ResourceModel\Schedule\Expression\Part\ParserFactory;
use Magento\Cron\Model\ResourceModel\Schedule\Expression\Part\ParserInterface;
use Magento\Cron\Model\ResourceModel\Schedule\Expression\PartInterface;

/**
 * Cron expression sub part validator handler class
 *
 * @api
 */
class Modulus implements ValidatorHandlerInterface
{
    /**
     * @var ParserInterface
     */
    private $parser;

    /**
     * @var NumericParserFactory
     */
    private $numericFactory;

    /**
     * Modulus constructor.
     *
     * @param ParserInterface      $parser
     * @param NumericParserFactory $numericFactory
     */
    public function __construct(
        ParserInterface $parser,
        NumericParserFactory $numericFactory
    ) {
        $this->parser = $parser;
        $this->numericFactory = $numericFactory;
    }

    /**
     * Handle cron expression sub part
     *
     * Returns
     * - If valid:
     *   - original/modified $subPartValue, to continue processing other handles
     *   - true, to stop executing next handles
     * - If not valid
     *   - false, to stop executing next handles
     *
     * @param PartInterface $part
     * @param string        $subPartValue
     *
     * @return string|bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function handle(PartInterface $part, $subPartValue)
    {
        $subPartValues = $this->parser->parse($subPartValue, ParserFactory::MODULUS_PARSER);

        if (count($subPartValues) > 1) {
            if (count($subPartValues) !== 2) {
                return false;
            }

            if (!is_numeric($subPartValues[1])) {
                return false;
            }

            return $subPartValues[0];
        }

        return $subPartValue;
    }
}

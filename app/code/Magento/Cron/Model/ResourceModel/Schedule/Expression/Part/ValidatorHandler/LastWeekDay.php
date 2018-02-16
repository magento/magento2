<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cron\Model\ResourceModel\Schedule\Expression\Part\ValidatorHandler;

use Magento\Cron\Model\ResourceModel\Schedule\Expression\Part\NumericParserFactory;
use Magento\Cron\Model\ResourceModel\Schedule\Expression\PartInterface;

/**
 * Cron expression sub part validator handler class
 *
 * @api
 */
class LastWeekDay implements ValidatorHandlerInterface
{
    const MATCH_CHAR = 'L';

    /**
     * @var NumericParserFactory
     */
    private $numericFactory;

    /**
     * Validator constructor.
     *
     * @param NumericParserFactory $numericFactory
     */
    public function __construct(
        NumericParserFactory $numericFactory
    ) {
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
     */
    public function handle(PartInterface $part, $subPartValue)
    {
        $numeric = $this->numericFactory->create($part->getNumericParser());
        $regexp = '/^[' . $numeric->getRangeMin() . '-' . $numeric->getRangeMax() . ']' . self::MATCH_CHAR . '$/';
        return (bool)preg_match($regexp, $subPartValue) ?: $subPartValue;
    }
}

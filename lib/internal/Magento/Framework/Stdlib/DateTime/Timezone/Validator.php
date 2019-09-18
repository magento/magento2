<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Stdlib\DateTime\Timezone;

use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Phrase;

/**
 * @api
 * @since 100.0.2
 */
class Validator
{
    /**
     * Maximum allowed year value
     *
     * @var int
     */
    protected $_yearMaxValue;

    /**
     * Minimum allowed year value
     *
     * @var int
     */
    protected $_yearMinValue;

    /**
     * @param int $yearMinValue
     * @param int $yearMaxValue
     */
    public function __construct(
        $yearMinValue = \Magento\Framework\Stdlib\DateTime::YEAR_MIN_VALUE,
        $yearMaxValue = \Magento\Framework\Stdlib\DateTime::YEAR_MAX_VALUE
    ) {
        $this->_yearMaxValue = $yearMaxValue;
        $this->_yearMinValue = $yearMinValue;
    }

    /**
     * Validate timestamp
     *
     * @param int|string $timestamp
     * @param int|string $toDate
     * @return void
     * @throws \Magento\Framework\Exception\ValidatorException
     */
    public function validate($timestamp, $toDate)
    {
        $transitionYear = date('Y', $timestamp);

        if ($transitionYear > $this->_yearMaxValue || $transitionYear < $this->_yearMinValue) {
            throw new ValidatorException(
                new Phrase(
                    "The transition year isn't included in the system date range. "
                    . "Verify the year date range and try again."
                )
            );
        }

        if ((int) $timestamp > (int) $toDate) {
            throw new ValidatorException(
                new Phrase('Transition year is out of specified date range.')
            );
        }
    }
}

<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright  Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\Stdlib\DateTime\Timezone;

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
     * @throws ValidationException
     */
    public function validate($timestamp, $toDate)
    {
        $transitionYear = date('Y', $timestamp);

        if ($transitionYear > $this->_yearMaxValue || $transitionYear < $this->_yearMinValue) {
            throw new ValidationException('Transition year is out of system date range.');
        }

        if ((int) $timestamp > (int) $toDate) {
            throw new ValidationException('Transition year is out of specified date range.');
        }
    }
}

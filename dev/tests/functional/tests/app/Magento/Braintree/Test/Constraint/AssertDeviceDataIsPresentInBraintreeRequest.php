<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Braintree\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Mtf\Util\Command\File\Log;

/**
 * Assert that device data is present in Braintree request.
 */
class AssertDeviceDataIsPresentInBraintreeRequest extends AbstractConstraint
{
    /**
     * Log file name.
     */
    const FILE_NAME = 'debug.log';

    /**
     * Device data pattern for regular expression.
     */
    const DEVICE_DATA_PATTERN = '/\'deviceData\' => \'{"device_session_id":"\w*","fraud_merchant_id":"\w*"}\'/';

    /**
     * Assert that device data is present in Braintree request.
     *
     * @param Log $log
     * @return void
     */
    public function processAssert(Log $log)
    {
        $file = $log->getFileContent(self::FILE_NAME);
        \PHPUnit_Framework_Assert::assertRegExp(
            self::DEVICE_DATA_PATTERN,
            $file,
            'The device data is not present in Braintree request.'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'The device data is present in Braintree request.';
    }
}

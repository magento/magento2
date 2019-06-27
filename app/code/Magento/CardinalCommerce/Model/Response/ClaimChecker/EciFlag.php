<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CardinalCommerce\Model\Response\ClaimChecker;

use Magento\Framework\Jwt\ClaimCheckerInterface;
use Magento\Framework\Jwt\InvalidClaimException;

/**
 * Checks 3DS status of transaction from ECI Flag value. Liability shift applies.
 */
class EciFlag implements ClaimCheckerInterface
{
    /**
     * 3DS status of transaction from ECI Flag value. Liability shift applies.
     *
     * 05 - Successful 3D Authentication (Visa, AMEX, JCB)
     * 02 - Successful 3D Authentication (MasterCard)
     * 06 - Attempted Processing or User Not Enrolled (Visa, AMEX, JCB)
     * 01 - Attempted Processing or User Not Enrolled (MasterCard)
     * 07 - 3DS authentication is either failed or could not be attempted;
     *      possible reasons being both card and Issuing Bank are not secured by 3DS,
     *      technical errors, or improper configuration. (Visa, AMEX, JCB)
     * 00 - 3DS authentication is either failed or could not be attempted;
     *      possible reasons being both card and Issuing Bank are not secured by 3DS,
     *      technical errors, or improper configuration. (MasterCard)
     *
     * @var array
     */
    private $allowedECIFlag = ['05', '02', '06', '01'];

    /**
     * @inheritdoc
     */
    public function checkClaim($value): void
    {
        if (!in_array($value, $this->allowedECIFlag)) {
            throw new InvalidClaimException('Invalid ECI Flag.', $this->supportedClaim(), $value);
        }
    }

    /**
     * @inheritdoc
     */
    public function supportedClaim(): string
    {
        return 'ECIFlag';
    }
}

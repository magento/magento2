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
 * Checks the state of the transaction
 */
class ActionCode implements ClaimCheckerInterface
{
    /**
     * Resulting state of the transaction.
     *
     *  SUCCESS  - The transaction resulted in success for the payment type used. For example,
     *             with a CCA transaction this would indicate the user has successfully completed authentication.
     *
     *  NOACTION - The transaction was successful but requires in no additional action. For example,
     *             with a CCA transaction this would indicate that the user is not currently enrolled in 3D Secure,
     *             but the API calls were successful.
     *
     *  FAILURE  - The transaction resulted in an error. For example, with a CCA transaction this would indicate
     *             that the user failed authentication or an error was encountered while processing the transaction.
     *
     *  ERROR    - A service level error was encountered. These are generally reserved for connectivity
     *             or API authentication issues. For example if your JWT was incorrectly signed, or Cardinal
     *             services are currently unreachable.
     *
     * @var array
     */
    private $allowedActionCode = ['SUCCESS', 'NOACTION'];

    /**
     * @inheritdoc
     */
    public function checkClaim($value): void
    {
        if (!in_array($value, $this->allowedActionCode)) {
            throw new InvalidClaimException('Invalid Action Code.', $this->supportedClaim(), $value);
        }
    }

    /**
     * @inheritdoc
     */
    public function supportedClaim(): string
    {
        return 'ActionCode';
    }
}

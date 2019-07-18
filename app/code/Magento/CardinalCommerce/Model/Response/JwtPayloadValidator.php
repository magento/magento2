<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CardinalCommerce\Model\Response;

use Magento\Framework\Intl\DateTimeFactory;

/**
 * Validates payload of CardinalCommerce response JWT.
 */
class JwtPayloadValidator implements JwtPayloadValidatorInterface
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
     * 3DS status of transaction from ECI Flag value. Liability shift applies.
     *
     *  05 - Successful 3D Authentication (Visa, AMEX, JCB)
     *  02 - Successful 3D Authentication (MasterCard)
     *  06 - Attempted Processing or User Not Enrolled (Visa, AMEX, JCB)
     *  01 - Attempted Processing or User Not Enrolled (MasterCard)
     *  07 - 3DS authentication is either failed or could not be attempted;
     *       possible reasons being both card and Issuing Bank are not secured by 3DS,
     *       technical errors, or improper configuration. (Visa, AMEX, JCB)
     *  00 - 3DS authentication is either failed or could not be attempted;
     *       possible reasons being both card and Issuing Bank are not secured by 3DS,
     *       technical errors, or improper configuration. (MasterCard)
     *
     * @var array
     */
    private $allowedECIFlag = ['05', '02', '06', '01'];

    /**
     * @var DateTimeFactory
     */
    private $dateTimeFactory;

    /**
     * @param DateTimeFactory $dateTimeFactory
     */
    public function __construct(
        DateTimeFactory $dateTimeFactory
    ) {
        $this->dateTimeFactory = $dateTimeFactory;
    }
    /**
     * @inheritdoc
     */
    public function validate(array $jwtPayload): bool
    {
        $transactionState = $jwtPayload['Payload']['ActionCode'] ?? '';
        $errorNumber = $jwtPayload['Payload']['ErrorNumber'] ?? -1;
        $eciFlag = $jwtPayload['Payload']['Payment']['ExtendedData']['ECIFlag'] ?? '';
        $expTimestamp = $jwtPayload['exp'] ?? 0;

        return $this->isValidErrorNumber((int)$errorNumber)
            && $this->isValidTransactionState($transactionState)
            && $this->isValidEciFlag($eciFlag)
            && $this->isNotExpired((int)$expTimestamp);
    }

    /**
     * Checks application error number.
     *
     * A non-zero value represents the error encountered while attempting the process the message request.
     *
     * @param int $errorNumber
     * @return bool
     */
    private function isValidErrorNumber(int $errorNumber)
    {
        return $errorNumber === 0;
    }

    /**
     * Checks if value of transaction state identifier is in allowed list.
     *
     * @param string $transactionState
     * @return bool
     */
    private function isValidTransactionState(string $transactionState)
    {
        return in_array($transactionState, $this->allowedActionCode);
    }

    /**
     * Checks if value of ECI Flag identifier is in allowed list.
     *
     * @param string $eciFlag
     * @return bool
     */
    private function isValidEciFlag(string $eciFlag)
    {
        return in_array($eciFlag, $this->allowedECIFlag);
    }

    /**
     * Checks if token is not expired.
     *
     * @param int $expTimestamp
     * @return bool
     */
    private function isNotExpired(int $expTimestamp)
    {
        $currentDate = $this->dateTimeFactory->create('now', new \DateTimeZone('UTC'));

        return $currentDate->getTimestamp() < $expTimestamp;
    }
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AuthorizenetAcceptjs\Gateway\Validator;

use Magento\AuthorizenetAcceptjs\Gateway\Config;
use Magento\AuthorizenetAcceptjs\Gateway\SubjectReader;
use Magento\Framework\Encryption\Helper\Security;
use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterface;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;

/**
 * Validates the transaction hash
 */
class TransactionHashValidator extends AbstractValidator
{
    /**
     * The error code for failed transaction hash verification
     */
    private const ERROR_TRANSACTION_HASH = 'ETHV';

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @var Config
     */
    private $config;

    /**
     * @param ResultInterfaceFactory $resultFactory
     * @param SubjectReader $subjectReader
     * @param Config $config
     */
    public function __construct(
        ResultInterfaceFactory $resultFactory,
        SubjectReader $subjectReader,
        Config $config
    ) {
        parent::__construct($resultFactory);

        $this->subjectReader = $subjectReader;
        $this->config = $config;
    }

    /**
     * Validates the transaction hash matches the configured hash
     *
     * @param array $validationSubject
     * @return ResultInterface
     */
    public function validate(array $validationSubject): ResultInterface
    {
        $response = $this->subjectReader->readResponse($validationSubject);
        $storeId = $this->subjectReader->readStoreId($validationSubject);

        if (!empty($response['transactionResponse']['transHashSha2'])) {
            return $this->validateHash(
                $validationSubject,
                $this->config->getTransactionSignatureKey($storeId),
                'transHashSha2',
                'generateSha512Hash'
            );
        } elseif (!empty($response['transactionResponse']['transHash'])) {
            return $this->validateHash(
                $validationSubject,
                $this->config->getLegacyTransactionHash($storeId),
                'transHash',
                'generateMd5Hash'
            );
        }

        return $this->createResult(
            false,
            [
                __('The authenticity of the gateway response could not be verified.')
            ],
            [self::ERROR_TRANSACTION_HASH]
        );
    }

    /**
     * Validates the response again the legacy MD5 spec
     *
     * @param array $validationSubject
     * @param string $storedHash
     * @param string $hashField
     * @param string $generateFunction
     * @return ResultInterface
     */
    private function validateHash(
        array $validationSubject,
        string $storedHash,
        string $hashField,
        string $generateFunction
    ): ResultInterface {
        $storeId = $this->subjectReader->readStoreId($validationSubject);
        $response = $this->subjectReader->readResponse($validationSubject);
        $transactionResponse = $response['transactionResponse'];

        /*
         * Authorize.net is inconsistent with how they hash. Refund uses the amount when referencing a transaction
         * but will use 0 when voiding or when refunding without a reference.
         */
        if (empty($transactionResponse['refTransID'])
            || (!empty($transactionResponse['transId'])
                // Yes, their naming uses inconsistent letter casing for transId and refTransID.
                && $transactionResponse['refTransID'] !== $transactionResponse['transId'])
        ) {
            try {
                $amount = $this->subjectReader->readAmount($validationSubject);
            } catch (\InvalidArgumentException $e) {
                // Void will not contain the amount and will use 0.00 for hashing
                $amount = 0;
            }
        } else {
            // Non-refund reference transactions (void) don't use the amount
            $amount = 0;
        }

        $hash = $this->{$generateFunction}(
            $storedHash,
            $this->config->getLoginId($storeId),
            sprintf('%.2F', $amount),
            $transactionResponse['transId'] ?? ''
        );

        if (Security::compareStrings($hash, $transactionResponse[$hashField])) {
            return $this->createResult(true);
        }

        return $this->createResult(
            false,
            [
                __('The authenticity of the gateway response could not be verified.')
            ],
            [self::ERROR_TRANSACTION_HASH]
        );
    }

    /**
     * Generates a Md5 hash to compare against AuthNet's.
     *
     * @param string $merchantMd5
     * @param string $merchantApiLogin
     * @param string $amount
     * @param string $transactionId
     * @return string
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     */
    private function generateMd5Hash(
        $merchantMd5,
        $merchantApiLogin,
        $amount,
        $transactionId
    ) {
        return strtoupper(md5($merchantMd5 . $merchantApiLogin . $transactionId . $amount));
    }

    /**
     * Generates a SHA-512 hash to compare against AuthNet's.
     *
     * @param string $merchantKey
     * @param string $merchantApiLogin
     * @param string $amount
     * @param string $transactionId
     * @return string
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     */
    private function generateSha512Hash(
        $merchantKey,
        $merchantApiLogin,
        $amount,
        $transactionId
    ) {
        $message = '^' . $merchantApiLogin . '^' . $transactionId . '^' . $amount . '^';

        return strtoupper(hash_hmac('sha512', $message, pack('H*', $merchantKey)));
    }
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CardinalCommerce\Model\Response;

use Magento\CardinalCommerce\Model\JwtManagement;
use Magento\CardinalCommerce\Model\Config;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;
use Magento\Payment\Model\Method\Logger as PaymentLogger;

/**
 * Parses content of CardinalCommerce response JWT.
 */
class JwtParser implements JwtParserInterface
{
    /**
     * @var JwtManagement
     */
    private $jwtManagement;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var JwtPayloadValidatorInterface
     */
    private $tokenValidator;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var PaymentLogger
     */
    private $paymentLogger;

    /**
     * @param JwtManagement $jwtManagement
     * @param Config $config
     * @param JwtPayloadValidatorInterface $tokenValidator
     * @param PaymentLogger $paymentLogger
     * @param LoggerInterface $logger
     */
    public function __construct(
        JwtManagement $jwtManagement,
        Config $config,
        JwtPayloadValidatorInterface $tokenValidator,
        PaymentLogger $paymentLogger,
        LoggerInterface $logger
    ) {
        $this->jwtManagement = $jwtManagement;
        $this->config = $config;
        $this->tokenValidator = $tokenValidator;
        $this->paymentLogger = $paymentLogger;
        $this->logger = $logger;
    }

    /**
     * Returns response JWT payload.
     *
     * @param string $jwt
     * @return array
     * @throws LocalizedException
     */
    public function execute(string $jwt): array
    {
        $jwtPayload = '';
        try {
            $this->debug(['Cardinal Response JWT:' => $jwt]);
            $jwtPayload = $this->jwtManagement->decode($jwt, $this->config->getApiKey());
            $this->debug(['Cardinal Response JWT payload:' => $jwtPayload]);
            if (!$this->tokenValidator->validate($jwtPayload)) {
                $this->throwException();
            }
        } catch (\InvalidArgumentException $e) {
            $this->logger->critical($e, ['CardinalCommerce3DSecure']);
            $this->throwException();
        }

        return $jwtPayload;
    }

    /**
     * Log JWT data.
     *
     * @param array $data
     * @return void
     */
    private function debug(array $data)
    {
        if ($this->config->isDebugModeEnabled()) {
            $this->paymentLogger->debug($data, ['iss'], true);
        }
    }

    /**
     * Throw general localized exception.
     *
     * @return void
     * @throws LocalizedException
     */
    private function throwException()
    {
        throw new LocalizedException(
            __(
                'Authentication Failed. Your card issuer cannot authenticate this card. ' .
                'Please select another card or form of payment to complete your purchase.'
            )
        );
    }
}

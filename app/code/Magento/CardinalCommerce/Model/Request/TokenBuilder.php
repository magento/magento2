<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CardinalCommerce\Model\Request;

use Magento\CardinalCommerce\Model\JwtManagement;
use Magento\CardinalCommerce\Model\Config;
use Magento\Checkout\Model\Session;
use Magento\Framework\DataObject\IdentityGeneratorInterface;
use Magento\Framework\Intl\DateTimeFactory;

/**
 * Cardinal request token builder.
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class TokenBuilder
{
    /**
     * @var JwtManagement
     */
    private $jwtManagement;

    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var IdentityGeneratorInterface
     */
    private $identityGenerator;

    /**
     * @var DateTimeFactory
     */
    private $dateTimeFactory;

    /**
     * @param JwtManagement $jwtManagement
     * @param Session $checkoutSession
     * @param Config $config
     * @param IdentityGeneratorInterface $identityGenerator
     * @param DateTimeFactory $dateTimeFactory
     */
    public function __construct(
        JwtManagement $jwtManagement,
        Session $checkoutSession,
        Config $config,
        IdentityGeneratorInterface $identityGenerator,
        DateTimeFactory $dateTimeFactory
    ) {
        $this->jwtManagement = $jwtManagement;
        $this->checkoutSession = $checkoutSession;
        $this->config = $config;
        $this->identityGenerator = $identityGenerator;
        $this->dateTimeFactory = $dateTimeFactory;
    }

    /**
     * Builds request JWT.
     *
     * @return string
     */
    public function build()
    {
        $quote = $this->checkoutSession->getQuote();
        $currentDate = $this->dateTimeFactory->create('now', new \DateTimeZone('UTC'));
        $orderDetails = [
            'OrderDetails' => [
                'OrderNumber' => $quote->getId(),
                'Amount' => $quote->getBaseGrandTotal() * 100,
                'CurrencyCode' => $quote->getBaseCurrencyCode()
            ]
        ];

        $token = [
            'jti' => $this->identityGenerator->generateId(),
            'iss' => $this->config->getApiIdentifier(),
            'iat' => $currentDate->getTimestamp(),
            'OrgUnitId' => $this->config->getOrgUnitId(),
            'Payload' => $orderDetails,
            'ObjectifyPayload' => true
        ];

        $jwt = $this->jwtManagement->encode($token, $this->config->getApiKey());

        return $jwt;
    }
}

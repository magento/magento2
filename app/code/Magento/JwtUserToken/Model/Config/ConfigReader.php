<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\JwtUserToken\Model\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\JwtUserToken\Api\ConfigReaderInterface;

/**
 * provides JWT configuration values.
 */
class ConfigReader implements ConfigReaderInterface
{
    private const JWT_ALG_CONFIG_PATH = 'webapi/jwtauth/jwt_alg';

    private const JWE_ALG_CONFIG_PATH = 'webapi/jwtauth/jwe_alg';

    private const CUSTOMER_EXPIRATION_CONFIG_PATH = 'webapi/jwtauth/customer_expiration';

    private const ADMIN_EXPIRATION_CONFIG_PATH = 'webapi/jwtauth/admin_expiration';

    /**
     * @var ScopeConfigInterface
     */
    private $config;

    /**
     * @var JwtAlgorithmSource
     */
    private $algSource;

    /**
     * @param ScopeConfigInterface $config
     * @param JwtAlgorithmSource $algorithmSource
     */
    public function __construct(ScopeConfigInterface $config, JwtAlgorithmSource $algorithmSource)
    {
        $this->config = $config;
        $this->algSource = $algorithmSource;
    }

    /**
     * @inheritDoc
     */
    public function getJwtAlgorithm(): string
    {
        return $this->config->getValue(self::JWT_ALG_CONFIG_PATH);
    }

    /**
     * @inheritDoc
     */
    public function getJweContentAlgorithm(): string
    {
        return $this->config->getValue(self::JWE_ALG_CONFIG_PATH);
    }

    /**
     * @inheritDoc
     */
    public function getCustomerTtl(): int
    {
        $ttl = (int) $this->config->getValue(self::CUSTOMER_EXPIRATION_CONFIG_PATH);
        if ($ttl <= 0) {
            $ttl = 30;
        }

        return $ttl;
    }

    /**
     * @inheritDoc
     */
    public function getAdminTtl(): int
    {
        $ttl = (int) $this->config->getValue(self::ADMIN_EXPIRATION_CONFIG_PATH);
        if ($ttl <= 0) {
            $ttl = 30;
        }

        return $ttl;
    }

    /**
     * @inheritDoc
     */
    public function getJwtAlgorithmType(string $algorithm): int
    {
        return $this->algSource->getAlgorithmType($algorithm);
    }
}

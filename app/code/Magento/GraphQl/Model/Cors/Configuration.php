<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Model\Cors;

use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Configuration provider for GraphQL CORS settings
 */
class Configuration implements ConfigurationInterface
{
    public const XML_PATH_CORS_HEADERS_ENABLED = 'graphql/cors/enabled';
    public const XML_PATH_CORS_ALLOWED_ORIGINS = 'graphql/cors/allowed_origins';
    public const XML_PATH_CORS_ALLOWED_HEADERS = 'graphql/cors/allowed_headers';
    public const XML_PATH_CORS_ALLOWED_METHODS = 'graphql/cors/allowed_methods';
    public const XML_PATH_CORS_MAX_AGE = 'graphql/cors/max_age';
    public const XML_PATH_CORS_ALLOW_CREDENTIALS = 'graphql/cors/allow_credentials';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Are CORS headers enabled
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_CORS_HEADERS_ENABLED);
    }

    /**
     * Get allowed origins or null if stored configuration is empty
     *
     * @return string|null
     */
    public function getAllowedOrigins(): ?string
    {
        return $this->scopeConfig->getValue(self::XML_PATH_CORS_ALLOWED_ORIGINS);
    }

    /**
     * Get allowed headers or null if stored configuration is empty
     *
     * @return string|null
     */
    public function getAllowedHeaders(): ?string
    {
        return $this->scopeConfig->getValue(self::XML_PATH_CORS_ALLOWED_HEADERS);
    }

    /**
     * Get allowed methods or null if stored configuration is empty
     *
     * @return string|null
     */
    public function getAllowedMethods(): ?string
    {
        return $this->scopeConfig->getValue(self::XML_PATH_CORS_ALLOWED_METHODS);
    }

    /**
     * Get max age header value
     *
     * @return int
     */
    public function getMaxAge(): int
    {
        return (int) $this->scopeConfig->getValue(self::XML_PATH_CORS_MAX_AGE);
    }

    /**
     * Are credentials allowed
     *
     * @return bool
     */
    public function isCredentialsAllowed(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_CORS_ALLOW_CREDENTIALS);
    }
}

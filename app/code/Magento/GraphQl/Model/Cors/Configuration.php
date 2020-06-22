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
    const XML_PATH_CORS_HEADERS_ENABLED = 'graphql/cors/enabled';
    const XML_PATH_CORS_ALLOWED_ORIGINS = 'graphql/cors/allowed_origins';
    const XML_PATH_CORS_ALLOWED_HEADERS = 'graphql/cors/allowed_headers';
    const XML_PATH_CORS_ALLOWED_METHODS = 'graphql/cors/allowed_methods';
    const XML_PATH_CORS_MAX_AGE = 'graphql/cors/max_age';
    const XML_PATH_CORS_ALLOW_CREDENTIALS = 'graphql/cors/allow_credentials';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    public function isEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_CORS_HEADERS_ENABLED);
    }

    public function getAllowedOrigins(): ?string
    {
        return $this->scopeConfig->getValue(self::XML_PATH_CORS_ALLOWED_ORIGINS);
    }

    public function getAllowedHeaders(): ?string
    {
        return $this->scopeConfig->getValue(self::XML_PATH_CORS_ALLOWED_HEADERS);
    }

    public function getAllowedMethods(): ?string
    {
        return $this->scopeConfig->getValue(self::XML_PATH_CORS_ALLOWED_METHODS);
    }

    public function getMaxAge(): int
    {
        return (int) $this->scopeConfig->getValue(self::XML_PATH_CORS_MAX_AGE);
    }

    public function isCredentialsAllowed(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_CORS_ALLOW_CREDENTIALS);
    }

}

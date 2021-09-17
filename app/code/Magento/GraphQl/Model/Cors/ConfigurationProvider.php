<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Model\Cors;

use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Provides CORS Configuration
 */
class ConfigurationProvider implements ConfigurationProviderInterface
{
    /**
     * path which stores config for list of allowed origins
     */
    public const GRAPHQL_CORS_ALLOWED_ORIGINS = 'web/graphql/cors_allowed_origins';

    /**
     * path which stores config for list of allowed methods
     */
    public const GRAPHQL_CORS_ALLOWED_METHODS = 'web/graphql/cors_allowed_methods';

    /**
     * path which stores config for list of allowed headers
     */
    public const GRAPHQL_CORS_ALLOWED_HEADERS = 'web/graphql/cors_allowed_headers';

    /**
     * path which stores config for max age
     */
    public const GRAPHQL_CORS_MAX_AGE = 'web/graphql/cors_max_age';

    /**
     * path which stores config for credentials allowed
     */
    public const GRAPHQL_CORS_ALLOW_CREDENTIALS = 'web/graphql/cors_allow_credentials';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * ConfigurationProvider constructor.
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * returns the list of allowed origins
     *
     * @return array|null
     */
    public function getAllowedOrigins(): ?array
    {
        return $this->processOptions($this->scopeConfig->getValue(self::GRAPHQL_CORS_ALLOWED_ORIGINS));
    }

    /**
     * returns the list of allowed headers
     *
     * @return array|null
     */
    public function getAllowedHeaders(): ?array
    {
        return $this->processOptions($this->scopeConfig->getValue(self::GRAPHQL_CORS_ALLOWED_HEADERS));
    }

    /**
     * returns the list of allowed methods
     *
     * @return array|null
     */
    public function getAllowedMethods(): ?array
    {
        return $this->processOptions($this->scopeConfig->getValue(self::GRAPHQL_CORS_ALLOWED_METHODS));
    }

    /**
     * returns CORS max age value
     *
     * @return string
     */
    public function getMaxAge(): string
    {
        return $this->scopeConfig->getValue(self::GRAPHQL_CORS_MAX_AGE);
    }

    /**
     * returns credentials value
     *
     * @return bool
     */
    public function isCredentialsAllowed(): bool
    {
        return $this->scopeConfig->isSetFlag(self::GRAPHQL_CORS_ALLOW_CREDENTIALS);
    }

    /**
     * converts the comma separated values into array
     *
     * @param $option
     * @param string $delimiter
     * @return array
     */
    public function processOptions($option, $delimiter = ','): array
    {
        //if no config is provided in env.php
        if(!$option){
            $option = '';
        }

        $configurations = explode($delimiter, $option);
        $trimmedConfigurations = array_map(
            function ($trimmedConfiguration) {
                return trim($trimmedConfiguration);
            },
            $configurations
        );

        $trimmedConfigurations = array_values(array_filter($trimmedConfigurations, function ($trimmedConfiguration) {
            return !empty($trimmedConfiguration);
        }));

        return $trimmedConfigurations;
    }
}

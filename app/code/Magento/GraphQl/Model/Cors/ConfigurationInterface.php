<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Model\Cors;

/**
 * Interface for configuration provider for GraphQL CORS settings
 */
interface ConfigurationInterface
{
    /**
     * Are CORS headers enabled
     *
     * @return bool
     */
    public function isEnabled(): bool;

    /**
     * Get allowed origins or null if stored configuration is empty
     *
     * @return string|null
     */
    public function getAllowedOrigins(): ?string;

    /**
     * Get allowed headers or null if stored configuration is empty
     *
     * @return string|null
     */
    public function getAllowedHeaders(): ?string;

    /**
     * Get allowed methods or null if stored configuration is empty
     *
     * @return string|null
     */
    public function getAllowedMethods(): ?string;

    /**
     * Get max age header value
     *
     * @return int
     */
    public function getMaxAge(): int;

    /**
     * Are credentials allowed
     *
     * @return bool
     */
    public function isCredentialsAllowed() : bool;
}

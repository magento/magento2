<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Model\Cors;

interface ConfigurationProviderInterface
{

    /**
     * Get list of allowed origins or null
     *
     * @return array|null
     */
    public function getAllowedOrigins(): ?array;

    /**
     * Get list of allowed headers or null
     *
     * @return array|null
     */
    public function getAllowedHeaders(): ?array;

    /**
     * Get list of allowed methods or null
     *
     * @return array|null
     */
    public function getAllowedMethods(): ?array;

    /**
     * Get max age header value
     *
     * @return string
     */
    public function getMaxAge(): string;

    /**
     * Are credentials allowed
     *
     * @return bool
     */
    public function isCredentialsAllowed() : bool;
}

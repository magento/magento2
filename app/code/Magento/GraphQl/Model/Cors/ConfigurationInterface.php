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
    public function isEnabled() : bool;

    public function getAllowedOrigins() : ?string;

    public function getAllowedHeaders() : ?string;

    public function getAllowedMethods() : ?string;

    public function getMaxAge() : int;

    public function isCredentialsAllowed() : bool;
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl;

use Magento\Framework\GraphQl\Config\ConfigElementInterface;

/**
 * Access all GraphQL type information declared in the schema's configuration.
 * Data includes types, interfaces they implement, their arguments, and fields.
 *
 * @api
 */
interface ConfigInterface
{
    /**
     * Get config element as an object by its name.
     *
     * @param string $configElementName
     * @return ConfigElementInterface
     */
    public function getConfigElement(string $configElementName): ConfigElementInterface;

    /**
     * Return all type names declared in a GraphQL schema's configuration and their type.
     *
     * Format is ['name' => 'example value', 'type' = 'example value']
     *
     * @return array $types
     */
    public function getDeclaredTypes(): array;
}

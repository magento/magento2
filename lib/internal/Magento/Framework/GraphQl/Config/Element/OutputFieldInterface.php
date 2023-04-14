<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Config\Element;

/**
 * Defines contract for output fields data as GraphQL objects.
 *
 * @api
 */
interface OutputFieldInterface extends FieldInterface
{
    /**
     * Get the resolver for a given field. If no resolver is specified, return an empty string.
     *
     * @return string
     */
    public function getResolver(): string;

    /**
     * Get the list of arguments configured for the field. Return an empty array if no arguments are configured.
     *
     * @return Argument[]
     */
    public function getArguments(): array;
}

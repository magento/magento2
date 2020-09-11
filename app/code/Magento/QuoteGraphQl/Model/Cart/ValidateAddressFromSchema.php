<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Cart;

use Magento\Framework\GraphQl\Schema\Type\TypeRegistry;

/**
 * Validates address against required fields from schema
 */
class ValidateAddressFromSchema
{
    /**
     * @var TypeRegistry
     */
    private $typeRegistry;

    /**
     * @param TypeRegistry $typeRegistry
     */
    public function __construct(
        TypeRegistry $typeRegistry
    ) {
        $this->typeRegistry = $typeRegistry;
    }

    /**
     * Validate data from address against mandatory fields from graphql schema for address
     *
     * @param array $address
     * @return bool
     */
    public function execute(array $address = []) : bool
    {
        /** @var \Magento\Framework\GraphQL\Schema\Type\Input\InputObjectType $cartAddressInput */
        $cartAddressInput = $this->typeRegistry->get('CartAddressInput');
        $fields = $cartAddressInput->getFields();

        foreach ($fields as $field) {
            if ($field->getType() instanceof \Magento\Framework\GraphQL\Schema\Type\NonNull) {
                // an array key has to exist but it's value should not be null
                if (array_key_exists($field->name, $address)
                    && !is_array($address[$field->name])
                    && !isset($address[$field->name])) {
                    return false;
                }
            }
        }
        return true;
    }
}

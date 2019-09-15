<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Resolver;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Quote\Model\Quote;
use Magento\QuoteGraphQl\Model\Cart\ExtractQuoteAddressData;
use Magento\Framework\GraphQl\Schema\Type\TypeRegistry;
use Magento\Framework\App\ObjectManager;

/**
 * @inheritdoc
 */
class ShippingAddresses implements ResolverInterface
{
    /**
     * @var ExtractQuoteAddressData
     */
    private $extractQuoteAddressData;

    /**
     * @var TypeRegistry
     */
    private $typeRegistry;

    /**
     * @param ExtractQuoteAddressData $extractQuoteAddressData
     * @param TypeRegistry|null $typeRegistry
     */
    public function __construct(
        ExtractQuoteAddressData $extractQuoteAddressData,
        TypeRegistry $typeRegistry = null
    ) {
        $this->extractQuoteAddressData = $extractQuoteAddressData;
        $this->typeRegistry = $typeRegistry ?: ObjectManager::getInstance()->get(TypeRegistry::class);
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }
        /** @var Quote $cart */
        $cart = $value['model'];

        $addressesData = [];
        $shippingAddresses = $cart->getAllShippingAddresses();

        if (count($shippingAddresses)) {
            foreach ($shippingAddresses as $shippingAddress) {
                $address = $this->extractQuoteAddressData->execute($shippingAddress);

                if ($this->validateAddressFromSchema($address)) {
                    $addressesData[] = $address;
                }
            }
        }
        return $addressesData;
    }

    /**
     * Validate data from address against mandatory fields from graphql schema for address
     *
     * @param array $address
     * @return bool
     */
    private function validateAddressFromSchema(array $address) : bool
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

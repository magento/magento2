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
use Magento\Quote\Api\Data\CartInterface;
use Magento\QuoteGraphQl\Model\Cart\ExtractQuoteAddressData;

/**
 * @inheritdoc
 */
class BillingAddress implements ResolverInterface
{
    /**
     * @var ExtractQuoteAddressData
     */
    private $extractQuoteAddressData;

    /**
     * @param ExtractQuoteAddressData $extractQuoteAddressData
     */
    public function __construct(ExtractQuoteAddressData $extractQuoteAddressData)
    {
        $this->extractQuoteAddressData = $extractQuoteAddressData;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }
        /** @var CartInterface $cart */
        $cart = $value['model'];

        $billingAddress = $cart->getBillingAddress();
        if (null === $billingAddress) {
            return null;
        }

        $addressData = $this->extractQuoteAddressData->execute($billingAddress);
        return $addressData;
    }
}

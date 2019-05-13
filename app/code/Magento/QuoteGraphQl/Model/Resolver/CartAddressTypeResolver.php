<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Resolver;

use Magento\Customer\Model\Address\AbstractAddress;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Query\Resolver\TypeResolverInterface;
use Magento\Quote\Model\Quote\Address;

/**
 * @inheritdoc
 */
class CartAddressTypeResolver implements TypeResolverInterface
{
    /**
     * @inheritdoc
     */
    public function resolveType(array $data): string
    {
        if (!isset($data['model'])) {
            throw new LocalizedException(__('Missing key "model" in cart address data'));
        }
        /** @var Address $address */
        $address = $data['model'];

        if ($address->getAddressType() == AbstractAddress::TYPE_SHIPPING) {
            $addressType = 'ShippingCartAddress';
        } elseif ($address->getAddressType() == AbstractAddress::TYPE_BILLING) {
            $addressType = 'BillingCartAddress';
        } else {
            throw new LocalizedException(__('Unsupported cart address type'));
        }
        return $addressType;
    }
}

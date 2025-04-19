<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Model\Resolver;

use Magento\Customer\Model\Data\Customer;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\CustomerGraphQl\Model\Customer\Address\ExtractCustomerAddressData;

/**
 * Customers addresses field resolver
 */
class CustomerAddresses implements ResolverInterface
{
    /**
     * @var ExtractCustomerAddressData
     */
    private $extractCustomerAddressData;

    /**
     * @param ExtractCustomerAddressData $extractCustomerAddressData
     */
    public function __construct(
        ExtractCustomerAddressData $extractCustomerAddressData
    ) {
        $this->extractCustomerAddressData = $extractCustomerAddressData;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        ?array $value = null,
        ?array $args = null
    ) {
        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }
        /** @var Customer $customer */
        $customer = $value['model'];

        $addressesData = [];
        $addresses = $customer->getAddresses();

        if (count($addresses)) {
            foreach ($addresses as $address) {
                $addressesData[] = $this->extractCustomerAddressData->execute($address);
            }
        }
        return $addressesData;
    }
}

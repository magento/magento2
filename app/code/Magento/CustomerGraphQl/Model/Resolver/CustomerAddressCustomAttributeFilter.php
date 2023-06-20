<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Model\Resolver;

use Magento\CustomerGraphQl\Model\Customer\Address\ExtractCustomerAddressData;
use Magento\CustomerGraphQl\Model\Customer\Address\GetCustomerAddress;
use Magento\Framework\Api\CustomAttributesDataInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Resolver Customer Address Custom Attribute filter
 */
class CustomerAddressCustomAttributeFilter implements ResolverInterface
{
    /**
     * @var GetCustomerAddress
     */
    private GetCustomerAddress $getCustomerAddress;

    /**
     * @var ExtractCustomerAddressData
     */
    private ExtractCustomerAddressData $extractCustomerAddressData;

    /**
     * @param GetCustomerAddress $getCustomerAddress
     * @param ExtractCustomerAddressData $extractCustomerAddressData
     */
    public function __construct(
        GetCustomerAddress                $getCustomerAddress,
        ExtractCustomerAddressData        $extractCustomerAddressData,
    ) {
        $this->getCustomerAddress = $getCustomerAddress;
        $this->extractCustomerAddressData = $extractCustomerAddressData;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ): array {
        $customAttributes = $value[CustomAttributesDataInterface::CUSTOM_ATTRIBUTES . 'V2'];
        if (isset($args['uids']) && !empty($args['uids'])) {
            $selectedUids = array_values($args['uids']);
            return array_filter($customAttributes, function ($attr) use ($selectedUids) {
                return in_array($attr['uid'], $selectedUids);
            });
        }

        return $customAttributes;
    }
}

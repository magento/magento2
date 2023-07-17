<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Model\Resolver;

use Magento\CustomerGraphQl\Model\Customer\ExtractCustomerData;
use Magento\CustomerGraphQl\Model\Customer\GetCustomer;
use Magento\Framework\Api\CustomAttributesDataInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Resolver Custom Attribute filter
 */
class CustomAttributeFilter implements ResolverInterface
{
    /**
     * @var GetCustomer
     */
    private GetCustomer $getCustomer;

    /**
     * @var ExtractCustomerData
     */
    private ExtractCustomerData $extractCustomerData;

    /**
     * @param GetCustomer $getCustomer
     * @param ExtractCustomerData $extractCustomerData
     */
    public function __construct(
        GetCustomer                $getCustomer,
        ExtractCustomerData        $extractCustomerData,
    ) {
        $this->getCustomer = $getCustomer;
        $this->extractCustomerData = $extractCustomerData;
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
        $customAttributes = $value[CustomAttributesDataInterface::CUSTOM_ATTRIBUTES];
        if (isset($args['uids']) && !empty($args['uids'])) {
            $selectedUids = array_values($args['uids']);
            return array_filter($customAttributes, function ($attr) use ($selectedUids) {
                return in_array($attr['uid'], $selectedUids);
            });
        }

        return $customAttributes;
    }
}

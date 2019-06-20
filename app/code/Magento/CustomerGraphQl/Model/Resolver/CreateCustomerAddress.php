<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Model\Resolver;

use Magento\CustomerGraphQl\Model\Customer\Address\CreateCustomerAddress as CreateCustomerAddressModel;
use Magento\CustomerGraphQl\Model\Customer\Address\ExtractCustomerAddressData;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\GraphQl\Model\Query\ContextInterface;

/**
 * Customers address create, used for GraphQL request processing
 */
class CreateCustomerAddress implements ResolverInterface
{
    /**
     * @var CreateCustomerAddressModel
     */
    private $createCustomerAddress;

    /**
     * @var ExtractCustomerAddressData
     */
    private $extractCustomerAddressData;

    /**
     * @param CreateCustomerAddressModel $createCustomerAddress
     * @param ExtractCustomerAddressData $extractCustomerAddressData
     */
    public function __construct(
        CreateCustomerAddressModel $createCustomerAddress,
        ExtractCustomerAddressData $extractCustomerAddressData
    ) {
        $this->createCustomerAddress = $createCustomerAddress;
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
    ) {
        /** @var ContextInterface $context */
        if (false === $context->getExtensionAttributes()->isCustomer()) {
            throw new GraphQlAuthorizationException(__('The current customer isn\'t authorized.'));
        }

        if (!isset($args['input']) || !is_array($args['input']) || empty($args['input'])) {
            throw new GraphQlInputException(__('"input" value should be specified'));
        }

        $address = $this->createCustomerAddress->execute($context->getUserId(), $args['input']);
        return $this->extractCustomerAddressData->execute($address);
    }
}

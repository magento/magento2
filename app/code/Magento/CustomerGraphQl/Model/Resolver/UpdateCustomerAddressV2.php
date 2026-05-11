<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Model\Resolver;

use Magento\CustomerGraphQl\Model\Customer\Address\ExtractCustomerAddressData;
use Magento\CustomerGraphQl\Model\Customer\Address\UpdateCustomerAddress;
use Magento\CustomerGraphQl\Model\Customer\Address\GetCustomerAddressV2;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Query\Uid;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GraphQl\Model\Query\ContextInterface;

class UpdateCustomerAddressV2 implements ResolverInterface
{
    /**
     * UpdateCustomerAddressV2 Constructor
     *
     * @param GetCustomerAddressV2 $getCustomerAddress
     * @param UpdateCustomerAddress $updateCustomerAddress
     * @param ExtractCustomerAddressData $extractCustomerAddressData
     * @param Uid $uidEncoder
     */
    public function __construct(
        private readonly GetCustomerAddressV2       $getCustomerAddress,
        private readonly UpdateCustomerAddress      $updateCustomerAddress,
        private readonly ExtractCustomerAddressData $extractCustomerAddressData,
        private readonly Uid                        $uidEncoder
    ) {
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
    ): array {
        /** @var ContextInterface $context */
        if (false === $context->getExtensionAttributes()->getIsCustomer()) {
            throw new GraphQlAuthorizationException(__('The current customer isn\'t authorized.'));
        }

        if (empty($args['uid'])) {
            throw new GraphQlInputException(__('Address "uid" value must be specified'));
        }

        if (empty($args['input']) || !is_array($args['input'])) {
            throw new GraphQlInputException(__('"input" value must be specified'));
        }

        $address = $this->getCustomerAddress->execute(
            (int) $this->uidEncoder->decode((string) $args['uid']),
            $context->getUserId()
        );

        $this->updateCustomerAddress->execute($address, $args['input']);

        return $this->extractCustomerAddressData->execute($address);
    }
}

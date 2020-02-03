<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Model\Resolver;

use Magento\CustomerGraphQl\Model\Customer\Address\DeleteCustomerAddress as DeleteCustomerAddressModel;
use Magento\CustomerGraphQl\Model\Customer\Address\GetCustomerAddress;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\GraphQl\Model\Query\ContextInterface;

/**
 * Customers address delete, used for GraphQL request processing.
 */
class DeleteCustomerAddress implements ResolverInterface
{
    /**
     * @var GetCustomerAddress
     */
    private $getCustomerAddress;

    /**
     * @var DeleteCustomerAddressModel
     */
    private $deleteCustomerAddress;

    /**
     * @param GetCustomerAddress $getCustomerAddress
     * @param DeleteCustomerAddressModel $deleteCustomerAddress
     */
    public function __construct(
        GetCustomerAddress $getCustomerAddress,
        DeleteCustomerAddressModel $deleteCustomerAddress
    ) {
        $this->getCustomerAddress = $getCustomerAddress;
        $this->deleteCustomerAddress = $deleteCustomerAddress;
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
        if (false === $context->getExtensionAttributes()->getIsCustomer()) {
            throw new GraphQlAuthorizationException(__('The current customer isn\'t authorized.'));
        }

        $address = $this->getCustomerAddress->execute((int)$args['id'], $context->getUserId());
        $this->deleteCustomerAddress->execute($address);
        return true;
    }
}

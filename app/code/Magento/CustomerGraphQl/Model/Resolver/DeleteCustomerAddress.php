<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Model\Resolver;

use Magento\CustomerGraphQl\Model\Customer\Address\DeleteCustomerAddress as DeleteCustomerAddressModel;
use Magento\CustomerGraphQl\Model\Customer\Address\GetCustomerAddress;
use Magento\CustomerGraphQl\Model\Customer\GetCustomer;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;

/**
 * Customers address delete, used for GraphQL request processing.
 */
class DeleteCustomerAddress implements ResolverInterface
{
    /**
     * @var GetCustomer
     */
    private $getCustomer;

    /**
     * @var GetCustomerAddress
     */
    private $getCustomerAddress;

    /**
     * @var DeleteCustomerAddressModel
     */
    private $deleteCustomerAddress;

    /**
     * @param GetCustomer $getCustomer
     * @param GetCustomerAddress $getCustomerAddress
     * @param DeleteCustomerAddressModel $deleteCustomerAddress
     */
    public function __construct(
        GetCustomer $getCustomer,
        GetCustomerAddress $getCustomerAddress,
        DeleteCustomerAddressModel $deleteCustomerAddress
    ) {
        $this->getCustomer = $getCustomer;
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
        if (!isset($args['id']) || empty($args['id'])) {
            throw new GraphQlInputException(__('Address "id" value should be specified'));
        }

        $customer = $this->getCustomer->execute($context);
        $address = $this->getCustomerAddress->execute((int)$args['id'], (int)$customer->getId());

        $this->deleteCustomerAddress->execute($address);
        return true;
    }
}

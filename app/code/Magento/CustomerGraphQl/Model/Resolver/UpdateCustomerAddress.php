<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Model\Resolver;

use Magento\CustomerGraphQl\Model\Customer\Address\ExtractCustomerAddressData;
use Magento\CustomerGraphQl\Model\Customer\Address\GetCustomerAddress;
use Magento\CustomerGraphQl\Model\Customer\Address\UpdateCustomerAddress as UpdateCustomerAddressModel;
use Magento\CustomerGraphQl\Model\Customer\GetCustomer;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;

/**
 * Customers address update, used for GraphQL request processing
 */
class UpdateCustomerAddress implements ResolverInterface
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
     * @var UpdateCustomerAddressModel
     */
    private $updateCustomerAddress;

    /**
     * @var ExtractCustomerAddressData
     */
    private $extractCustomerAddressData;

    /**
     * @param GetCustomer $getCustomer
     * @param GetCustomerAddress $getCustomerAddress
     * @param UpdateCustomerAddressModel $updateCustomerAddress
     * @param ExtractCustomerAddressData $extractCustomerAddressData
     */
    public function __construct(
        GetCustomer $getCustomer,
        GetCustomerAddress $getCustomerAddress,
        UpdateCustomerAddressModel $updateCustomerAddress,
        ExtractCustomerAddressData $extractCustomerAddressData
    ) {
        $this->getCustomer = $getCustomer;
        $this->getCustomerAddress = $getCustomerAddress;
        $this->updateCustomerAddress = $updateCustomerAddress;
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
        if (!isset($args['id']) || empty($args['id'])) {
            throw new GraphQlInputException(__('Address "id" value should be specified'));
        }

        if (!isset($args['input']) || !is_array($args['input']) || empty($args['input'])) {
            throw new GraphQlInputException(__('"input" value should be specified'));
        }

        $customer = $this->getCustomer->execute($context);
        $address = $this->getCustomerAddress->execute((int)$args['id'], (int)$customer->getId());

        $this->updateCustomerAddress->execute($address, $args['input']);
        return $this->extractCustomerAddressData->execute($address);
    }
}

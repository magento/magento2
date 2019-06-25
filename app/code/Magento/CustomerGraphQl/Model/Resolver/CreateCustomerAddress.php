<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Model\Resolver;

use Magento\CustomerGraphQl\Model\Customer\Address\CreateCustomerAddress as CreateCustomerAddressModel;
use Magento\CustomerGraphQl\Model\Customer\Address\ExtractCustomerAddressData;
use Magento\CustomerGraphQl\Model\Customer\GetCustomer;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;

/**
 * Customers address create, used for GraphQL request processing
 */
class CreateCustomerAddress implements ResolverInterface
{
    /**
     * @var GetCustomer
     */
    private $getCustomer;

    /**
     * @var CreateCustomerAddressModel
     */
    private $createCustomerAddress;

    /**
     * @var ExtractCustomerAddressData
     */
    private $extractCustomerAddressData;

    /**
     * @param GetCustomer $getCustomer
     * @param CreateCustomerAddressModel $createCustomerAddress
     * @param ExtractCustomerAddressData $extractCustomerAddressData
     */
    public function __construct(
        GetCustomer $getCustomer,
        CreateCustomerAddressModel $createCustomerAddress,
        ExtractCustomerAddressData $extractCustomerAddressData
    ) {
        $this->getCustomer = $getCustomer;
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
        if (!isset($args['input']) || !is_array($args['input']) || empty($args['input'])) {
            throw new GraphQlInputException(__('"input" value should be specified'));
        }

        $customer = $this->getCustomer->execute($context);

        $address = $this->createCustomerAddress->execute((int)$customer->getId(), $args['input']);
        return $this->extractCustomerAddressData->execute($address);
    }
}

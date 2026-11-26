<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Model\Resolver;

use Magento\CustomerGraphQl\Model\Customer\Address\DeleteCustomerAddress as DeleteCustomerAddressModel;
use Magento\CustomerGraphQl\Model\Customer\Address\GetCustomerAddress;
use Magento\CustomerGraphQl\Model\Customer\ValidateCustomerAuthorization;
use Magento\Framework\App\ObjectManager;
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
     * @var ValidateCustomerAuthorization
     */
    private $validateCustomerAuthorization;

    /**
     * @param GetCustomerAddress $getCustomerAddress
     * @param DeleteCustomerAddressModel $deleteCustomerAddress
     * @param ValidateCustomerAuthorization|null $validateCustomerAuthorization
     */
    public function __construct(
        GetCustomerAddress $getCustomerAddress,
        DeleteCustomerAddressModel $deleteCustomerAddress,
        ?ValidateCustomerAuthorization $validateCustomerAuthorization = null
    ) {
        $this->getCustomerAddress = $getCustomerAddress;
        $this->deleteCustomerAddress = $deleteCustomerAddress;
        $this->validateCustomerAuthorization = $validateCustomerAuthorization
            ?? ObjectManager::getInstance()->get(ValidateCustomerAuthorization::class);
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
        /** @var ContextInterface $context */
        $this->validateCustomerAuthorization->execute($context);

        $address = $this->getCustomerAddress->execute((int)$args['id'], $context->getUserId());
        $this->deleteCustomerAddress->execute($address);
        return true;
    }
}

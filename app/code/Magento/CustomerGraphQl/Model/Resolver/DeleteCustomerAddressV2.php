<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Model\Resolver;

use Magento\CustomerGraphQl\Model\Customer\Address\DeleteCustomerAddressV2 as DeleteAddress;
use Magento\CustomerGraphQl\Model\Customer\Address\GetCustomerAddressV2;
use Magento\CustomerGraphQl\Model\Customer\ValidateCustomerAuthorization;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Query\Uid;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GraphQl\Model\Query\ContextInterface;

class DeleteCustomerAddressV2 implements ResolverInterface
{
    /**
     * @var ValidateCustomerAuthorization
     */
    private readonly ValidateCustomerAuthorization $validateCustomerAuthorization;

    /**
     * DeleteCustomerAddressV2 Constructor
     *
     * @param GetCustomerAddressV2 $getCustomerAddress
     * @param DeleteAddress $deleteCustomerAddress
     * @param Uid $uidEncoder
     * @param ValidateCustomerAuthorization|null $validateCustomerAuthorization
     */
    public function __construct(
        private readonly GetCustomerAddressV2 $getCustomerAddress,
        private readonly DeleteAddress        $deleteCustomerAddress,
        private readonly Uid                  $uidEncoder,
        ?ValidateCustomerAuthorization $validateCustomerAuthorization = null
    ) {
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
    ): bool {
        /** @var ContextInterface $context */
        $this->validateCustomerAuthorization->execute($context);

        if (empty($args['uid'])) {
            throw new GraphQlInputException(__('Address "uid" value must be specified'));
        }

        $address = $this->getCustomerAddress->execute(
            (int) $this->uidEncoder->decode((string) $args['uid']),
            $context->getUserId()
        );

        $this->deleteCustomerAddress->execute($address);

        return true;
    }
}

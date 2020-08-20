<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Model\Address\Validator;

use Magento\Customer\Model\Address\AbstractAddress;
use Magento\Customer\Model\Address\ValidatorInterface;
use Magento\Customer\Model\AddressFactory;

/**
 * Validates that current Address is related to given Customer.
 */
class Customer implements ValidatorInterface
{
    /**
     * @var AddressFactory
     */
    private $addressFactory;

    /**
     * @param AddressFactory $addressFactory
     */
    public function __construct(AddressFactory $addressFactory)
    {
        $this->addressFactory = $addressFactory;
    }

    /**
     * @inheritDoc
     */
    public function validate(AbstractAddress $address): array
    {
        $errors = [];

        if ($address->getId() !== null) {
            $addressCustomerId = $address->getParentId();
            $originalAddressModel = $this->addressFactory->create()->load($address->getId());

            if ($addressCustomerId !== null && (int) $originalAddressModel->getParentId() !== $addressCustomerId) {
                $errors[] = __(
                    'Provided customer ID "%customer_id" isn\'t related to current customer address.',
                    ['customer_id' => $addressCustomerId]
                );
            }
        }

        return $errors;
    }
}

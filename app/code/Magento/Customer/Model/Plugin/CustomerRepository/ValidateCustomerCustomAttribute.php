<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Model\Plugin\CustomerRepository;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Customer\Attribute\CompositeValidator;

/**
 * Plugin responsible for validation customer custom attributes before save.
 */
class ValidateCustomerCustomAttribute
{
    /**
     * @var CompositeValidator
     */
    private $compositeValidator;

    /**
     * @param CompositeValidator $compositeValidator
     */
    public function __construct(CompositeValidator $compositeValidator)
    {
        $this->compositeValidator = $compositeValidator;
    }

    /**
     * Validate customer custom attributes before save.
     *
     * @param CustomerRepositoryInterface $subject
     * @param CustomerInterface $customer
     * @param string|null $passwordHash
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSave(
        CustomerRepositoryInterface $subject,
        CustomerInterface $customer,
        $passwordHash = null
    ): void {
        foreach ($customer->getCustomAttributes() as $customAttribute) {
            $this->compositeValidator->validate($customAttribute);
        }
    }
}

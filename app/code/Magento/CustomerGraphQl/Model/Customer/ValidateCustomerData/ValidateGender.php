<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Model\Customer\ValidateCustomerData;

use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Model\Data\AttributeMetadata;
use Magento\CustomerGraphQl\Api\ValidateCustomerDataInterface;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;

/**
 * Validates gender value
 */
class ValidateGender implements ValidateCustomerDataInterface
{
    /**
     * @var CustomerMetadataInterface
     */
    private $customerMetadata;

    /**
     * ValidateGender constructor.
     *
     * @param CustomerMetadataInterface $customerMetadata
     */
    public function __construct(CustomerMetadataInterface $customerMetadata)
    {
        $this->customerMetadata = $customerMetadata;
    }

    /**
     * @inheritDoc
     */
    public function execute(array $customerData): void
    {
        if (isset($customerData['gender']) && $customerData['gender']) {
            /** @var AttributeMetadata $genderData */
            $options = $this->customerMetadata->getAttributeMetadata('gender')->getOptions();

            $isValid = false;
            foreach ($options as $optionData) {
                if ($optionData->getValue() && $optionData->getValue() == $customerData['gender']) {
                    $isValid = true;
                }
            }

            if (!$isValid) {
                throw new GraphQlInputException(
                    __('"%1" is not a valid gender value.', $customerData['gender'])
                );
            }
        }
    }
}

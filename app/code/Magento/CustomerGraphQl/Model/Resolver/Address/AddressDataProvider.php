<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Model\Resolver\Address;

use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Api\CustomAttributesDataInterface;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Model\ResourceModel\Customer as CustomerResourceModel;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\Webapi\ServiceOutputProcessor;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * Customer Address field data provider, used for GraphQL request processing.
 */
class AddressDataProvider
{
    /**
     * @var ServiceOutputProcessor
     */
    private $serviceOutputProcessor;

    /**
     * @var SerializerInterface
     */
    private $jsonSerializer;

    /**
     * @var CustomerResourceModel
     */
    private $customerResourceModel;

    /**
     * @var CustomerFactory
     */
    private $customerFactory;

    /**
     * @param ServiceOutputProcessor $serviceOutputProcessor
     * @param SerializerInterface $jsonSerializer
     * @param CustomerResourceModel $customerResourceModel
     * @param CustomerFactory $customerFactory
     */
    public function __construct(
        ServiceOutputProcessor $serviceOutputProcessor,
        SerializerInterface $jsonSerializer,
        CustomerResourceModel $customerResourceModel,
        CustomerFactory $customerFactory
    ) {
        $this->serviceOutputProcessor = $serviceOutputProcessor;
        $this->jsonSerializer = $jsonSerializer;
        $this->customerResourceModel = $customerResourceModel;
        $this->customerFactory = $customerFactory;
    }

    /**
     * Transform single customer address data from object to in array format
     *
     * @param AddressInterface $addressObject
     * @return array
     */
    public function processCustomerAddress(AddressInterface $addressObject) : array
    {
        $address = $this->serviceOutputProcessor->process(
            $addressObject,
            AddressRepositoryInterface::class,
            'getById'
        );
        $customerModel = $this->customerFactory->create();
        $this->customerResourceModel->load($customerModel, $addressObject->getCustomerId());
        $address[CustomerInterface::DEFAULT_BILLING] =
            ($addressObject->getId() == $customerModel->getDefaultBillingAddress()->getId()) ? true : false;
        $address[CustomerInterface::DEFAULT_SHIPPING] =
            ($addressObject->getId() == $customerModel->getDefaultShippingAddress()->getId()) ? true : false;

        if (isset($address[CustomAttributesDataInterface::EXTENSION_ATTRIBUTES_KEY])) {
            $address = array_merge($address, $address[CustomAttributesDataInterface::EXTENSION_ATTRIBUTES_KEY]);
        }
        $customAttributes = [];
        if (isset($address[CustomAttributesDataInterface::CUSTOM_ATTRIBUTES])) {
            foreach ($address[CustomAttributesDataInterface::CUSTOM_ATTRIBUTES] as $attribute) {
                $isArray = false;
                if (is_array($attribute['value'])) {
                    $isArray = true;
                    foreach ($attribute['value'] as $attributeValue) {
                        if (is_array($attributeValue)) {
                            $customAttributes[$attribute['attribute_code']] = $this->jsonSerializer->serialize(
                                $attribute['value']
                            );
                            continue;
                        }
                        $customAttributes[$attribute['attribute_code']] = implode(',', $attribute['value']);
                        continue;
                    }
                }
                if ($isArray) {
                    continue;
                }
                $customAttributes[$attribute['attribute_code']] = $attribute['value'];
            }
        }
        $address = array_merge($address, $customAttributes);

        return $address;
    }
}

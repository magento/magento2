<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Model\Customer\Address;

use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Api\CustomAttributesDataInterface;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Model\ResourceModel\Customer as CustomerResourceModel;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\Webapi\ServiceOutputProcessor;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * Transform single customer address data from object to in array format
 */
class ExtractCustomerAddressData
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
     * Curate shipping and billing default options
     *
     * @param array $address
     * @param AddressInterface $addressObject
     * @return array
     */
    private function curateAddressDefaultValues(array $address, AddressInterface $addressObject) : array
    {
        $customerModel = $this->customerFactory->create();
        $this->customerResourceModel->load($customerModel, $addressObject->getCustomerId());
        $address[CustomerInterface::DEFAULT_BILLING] =
            ($customerModel->getDefaultBillingAddress()
                && $addressObject->getId() == $customerModel->getDefaultBillingAddress()->getId());
        $address[CustomerInterface::DEFAULT_SHIPPING] =
            ($customerModel->getDefaultShippingAddress()
                && $addressObject->getId() == $customerModel->getDefaultShippingAddress()->getId());
        return $address;
    }

    /**
     * Transform single customer address data from object to in array format
     *
     * @param AddressInterface $address
     * @return array
     */
    public function execute(AddressInterface $address): array
    {
        $addressData = $this->serviceOutputProcessor->process(
            $address,
            AddressRepositoryInterface::class,
            'getById'
        );
        $addressData = $this->curateAddressDefaultValues($addressData, $address);

        if (isset($addressData[CustomAttributesDataInterface::EXTENSION_ATTRIBUTES_KEY])) {
            $addressData = array_merge(
                $addressData,
                $addressData[CustomAttributesDataInterface::EXTENSION_ATTRIBUTES_KEY]
            );
        }
        $customAttributes = [];
        if (isset($addressData[CustomAttributesDataInterface::CUSTOM_ATTRIBUTES])) {
            foreach ($addressData[CustomAttributesDataInterface::CUSTOM_ATTRIBUTES] as $attribute) {
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
        $addressData = array_merge($addressData, $customAttributes);

        return $addressData;
    }
}

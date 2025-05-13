<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Model\Customer\Address;

use Magento\Customer\Api\AddressMetadataInterface;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\ResourceModel\Customer as CustomerResourceModel;
use Magento\EavGraphQl\Model\Output\Value\GetAttributeValueInterface;
use Magento\Framework\Api\CustomAttributesDataInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Webapi\ServiceOutputProcessor;

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
     * @var GetAttributeValueInterface
     */
    private GetAttributeValueInterface $getAttributeValue;

    /**
     * @param ServiceOutputProcessor $serviceOutputProcessor
     * @param SerializerInterface $jsonSerializer
     * @param CustomerResourceModel $customerResourceModel
     * @param CustomerFactory $customerFactory
     * @param GetAttributeValueInterface $getAttributeValue
     */
    public function __construct(
        ServiceOutputProcessor $serviceOutputProcessor,
        SerializerInterface $jsonSerializer,
        CustomerResourceModel $customerResourceModel,
        CustomerFactory $customerFactory,
        GetAttributeValueInterface $getAttributeValue
    ) {
        $this->serviceOutputProcessor = $serviceOutputProcessor;
        $this->jsonSerializer = $jsonSerializer;
        $this->customerResourceModel = $customerResourceModel;
        $this->customerFactory = $customerFactory;
        $this->getAttributeValue = $getAttributeValue;
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

        $customAttributes = isset($addressData[CustomAttributesDataInterface::CUSTOM_ATTRIBUTES])
            ? $this->formatCustomAttributes($addressData[CustomAttributesDataInterface::CUSTOM_ATTRIBUTES])
            : ['custom_attributesV2' => []];

        $addressData = array_merge($addressData, $customAttributes);

        $addressData['customer_id'] = null;

        if (isset($addressData['country_id'])) {
            $addressData['country_code'] = $addressData['country_id'];
        }

        return $addressData;
    }

    /**
     * Retrieve formatted custom attributes
     *
     * @param array $attributes
     * @return array
     */
    private function formatCustomAttributes(array $attributes)
    {
        foreach ($attributes as $attribute) {
            $isArray = false;
            if (is_array($attribute['value'])) {
                // @ignoreCoverageStart
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
                // @ignoreCoverageEnd
            }
            if ($isArray) {
                continue;
            }
            $customAttributes[$attribute['attribute_code']] = $attribute['value'];
        }

        $customAttributes['custom_attributesV2'] = array_map(
            function (array $customAttribute) {
                return $this->getAttributeValue->execute(
                    AddressMetadataInterface::ENTITY_TYPE_ADDRESS,
                    $customAttribute['attribute_code'],
                    $customAttribute['value']
                );
            },
            $attributes
        );
        usort($customAttributes['custom_attributesV2'], function (array $a, array $b) {
            $aPosition = $a['sort_order'];
            $bPosition = $b['sort_order'];
            return $aPosition <=> $bPosition;
        });

        return $customAttributes;
    }
}

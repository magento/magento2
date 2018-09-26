<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Model\Resolver\Address;

use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\AddressRepositoryInterface;
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
     * @param ServiceOutputProcessor $serviceOutputProcessor
     * @param SerializerInterface $jsonSerializer
     */
    public function __construct(
        ServiceOutputProcessor $serviceOutputProcessor,
        SerializerInterface $jsonSerializer
    ) {
        $this->serviceOutputProcessor = $serviceOutputProcessor;
        $this->jsonSerializer = $jsonSerializer;
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
        if (isset($address['extension_attributes'])) {
            $address = array_merge($address, $address['extension_attributes']);
        }
        $customAttributes = [];
        if (isset($address['custom_attributes'])) {
            foreach ($address['custom_attributes'] as $attribute) {
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

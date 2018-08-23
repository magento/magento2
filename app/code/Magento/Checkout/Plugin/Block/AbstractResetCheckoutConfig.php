<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Checkout\Plugin\Block;

use Magento\Checkout\Block\Cart\Shipping;
use Magento\Checkout\Block\Onepage;

/**
 * Class AbstractResetCheckoutConfig
 * Needed for reformat Customer Data address with custom attributes as options add labels for correct view on UI
 */
class AbstractResetCheckoutConfig
{
    /**
     * @var \Magento\Eav\Api\AttributeOptionManagementInterface
     */
    private $attributeOptionManager;

    /*
    * @var \Magento\Framework\Json\Helper\Data
    */
    private $serializer;

    /**
     * @param \Magento\Eav\Api\AttributeOptionManagementInterface $attributeOptionManager
     * @param \Magento\Framework\Serialize\SerializerInterface
     */
    public function __construct(
        \Magento\Eav\Api\AttributeOptionManagementInterface $attributeOptionManager,
        \Magento\Framework\Serialize\SerializerInterface $serializer
    )
    {
        $this->attributeOptionManager = $attributeOptionManager;
        $this->serializer = $serializer;
    }

    /**
     * After Get Checkout Config
     *
     * @param Onepage|Shipping $subject
     * @param mixed $result
     * @return string
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\StateException
     */
    protected function getSerializedCheckoutConfig($subject, $result)
    {
        $resultArray = $data = $this->serializer->unserialize($result);
        $customerAddresses = $resultArray['customerData']['addresses'];
        $hasAtLeastOneOptionAttribute = false;

        if (is_array($customerAddresses) && !empty($customerAddresses)) {
            foreach ($customerAddresses as $customerAddressIndex => $customerAddress) {
                if (!empty($customerAddress['custom_attributes'])) {
                    foreach ($customerAddress['custom_attributes'] as $customAttributeCode => $customAttribute) {
                        $attributeOptionLabels = $this->getAttributeLabels($customAttribute, $customAttributeCode);

                        if (!empty($attributeOptionLabels)) {
                            $hasAtLeastOneOptionAttribute = true;
                            $resultArray['customerData']['addresses'][$customerAddressIndex]['custom_attributes']
                            [$customAttributeCode]['label'] = implode(', ', $attributeOptionLabels);
                        }
                    }
                }
            }
        }

        return $hasAtLeastOneOptionAttribute ? $this->serializer->serialize($resultArray) : $result;
    }

    /**
     * Get Labels by CustomAttribute and CustomAttributeCode
     *
     * @param $customAttribute
     * @param $customAttributeCode
     * @return array
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\StateException
     */
    private function getAttributeLabels($customAttribute, $customAttributeCode)
    {
        $attributeOptionLabels = [];
        $customAttributeValues = explode(',', $customAttribute['value']);
        $attributeOptions = $this->attributeOptionManager->getItems(
            \Magento\Customer\Model\Indexer\Address\AttributeProvider::ENTITY,
            $customAttributeCode
        );

        if (!empty($attributeOptions)) {
            foreach ($attributeOptions as $attributeOption) {
                $attributeOptionValue = $attributeOption->getValue();
                if (in_array($attributeOptionValue, $customAttributeValues)) {
                    $attributeOptionLabels[] = $attributeOption->getLabel() ?? $attributeOptionValue;
                }
            }
        }

        return $attributeOptionLabels;
    }
}

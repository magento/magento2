<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Model\Customer;

use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\EavGraphQl\Model\GetAttributeValueComposite;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Webapi\ServiceOutputProcessor;

/**
 * Transform single customer data from object to in array format
 */
class ExtractCustomerData
{
    /**
     * @var ServiceOutputProcessor
     */
    private $serviceOutputProcessor;

    /**
     * @var GetAttributeValueComposite
     */
    private GetAttributeValueComposite $getAttributeValueComposite;

    /**
     * @param ServiceOutputProcessor $serviceOutputProcessor
     * @param GetAttributeValueComposite $getAttributeValueComposite
     */
    public function __construct(
        ServiceOutputProcessor $serviceOutputProcessor,
        GetAttributeValueComposite $getAttributeValueComposite
    ) {
        $this->serviceOutputProcessor = $serviceOutputProcessor;
        $this->getAttributeValueComposite = $getAttributeValueComposite;
    }

    /**
     * Curate default shipping and default billing keys
     *
     * @param array $arrayAddress
     * @return array
     */
    private function curateAddressData(array $arrayAddress): array
    {
        foreach ($arrayAddress as $key => $address) {
            if (!isset($address['default_shipping'])) {
                $arrayAddress[$key]['default_shipping'] = false;
            }
            if (!isset($address['default_billing'])) {
                $arrayAddress[$key]['default_billing'] = false;
            }
        }
        return $arrayAddress;
    }

    /**
     * Transform single customer data from object to in array format
     *
     * @param CustomerInterface $customer
     * @return array
     * @throws LocalizedException
     */
    public function execute(CustomerInterface $customer): array
    {
        $customerData = $this->serviceOutputProcessor->process(
            $customer,
            CustomerRepositoryInterface::class,
            'get'
        );
        $customerData['addresses'] = $this->curateAddressData($customerData['addresses']);
        if (isset($customerData['extension_attributes'])) {
            $customerData = array_merge($customerData, $customerData['extension_attributes']);
        }
        if (isset($customerData['custom_attributes'])) {
            $customerData['custom_attributes'] = array_map(
                function (array $customAttribute) {
                    return $this->getAttributeValueComposite->execute(
                        CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER,
                        $customAttribute
                    );
                },
                $customerData['custom_attributes']
            );
            usort($customerData['custom_attributes'], function (array $a, array $b) {
                $aPosition = $a['sort_order'];
                $bPosition = $b['sort_order'];
                return $aPosition <=> $bPosition;
            });
        } else {
            $customerData['custom_attributes'] = [];
        }
        //Fields are deprecated and should not be exposed on storefront.
        $customerData['group_id'] = null;
        $customerData['id'] = null;

        $customerData['model'] = $customer;

        //'dob' is deprecated, 'date_of_birth' is used instead.
        if (!empty($customerData['dob'])) {
            $customerData['date_of_birth'] = $customerData['dob'];
        }
        return $customerData;
    }
}

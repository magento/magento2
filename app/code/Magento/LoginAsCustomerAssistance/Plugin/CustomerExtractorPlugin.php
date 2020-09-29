<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomerAssistance\Plugin;

use Magento\Customer\Api\Data\CustomerExtensionFactory;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\CustomerExtractor;
use Magento\Framework\App\RequestInterface;

/**
 * Plugin for Magento\Customer\Model\CustomerExtractor.
 */
class CustomerExtractorPlugin
{
    /**
     * @var CustomerExtensionFactory
     */
    private $customerExtensionFactory;

    /**
     * @param CustomerExtensionFactory $customerExtensionFactory
     */
    public function __construct(
        CustomerExtensionFactory $customerExtensionFactory
    ) {
        $this->customerExtensionFactory = $customerExtensionFactory;
    }

    /**
     * Add assistance_allowed extension attribute value to Customer instance.
     *
     * @param CustomerExtractor $subject
     * @param CustomerInterface $customer
     * @param string $formCode
     * @param RequestInterface $request
     * @param array $attributeValues
     * @return CustomerInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterExtract(
        CustomerExtractor $subject,
        CustomerInterface $customer,
        string $formCode,
        RequestInterface $request,
        array $attributeValues = []
    ) {
        $assistanceAllowedStatus = $request->getParam('assistance_allowed');
        if (!empty($assistanceAllowedStatus)) {
            $extensionAttributes = $customer->getExtensionAttributes();
            if (null === $extensionAttributes) {
                $extensionAttributes = $this->customerExtensionFactory->create();
            }
            $extensionAttributes->setAssistanceAllowed((int)$assistanceAllowedStatus);
            $customer->setExtensionAttributes($extensionAttributes);
        }

        return $customer;
    }
}

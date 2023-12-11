<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */


namespace Magento\LoginAsCustomerGraphQl\Plugin;


use Magento\Customer\Api\Data\CustomerExtensionFactory;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\LoginAsCustomerAssistance\Model\IsAssistanceEnabled;

class DataObjectHelperPlugin
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
     * @param DataObjectHelper $subject
     * @param DataObjectHelper $result
     * @param mixed $dataObject
     * @param array $data
     * @param string $interfaceName
     * @return DataObjectHelper
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterPopulateWithArray(
        DataObjectHelper $subject,
        DataObjectHelper $result,
        Object $dataObject,
        array $data,
        string $interfaceName
    ) {
        if ($interfaceName === CustomerInterface::class
            && array_key_exists('allow_remote_shopping_assistance', $data)) {
            $isLoginAsCustomerEnabled = $data['allow_remote_shopping_assistance'];
            $extensionAttributes = $dataObject->getExtensionAttributes();
            if (null === $extensionAttributes) {
                $extensionAttributes = $this->customerExtensionFactory->create();
            }
            $extensionAttributes->setAssistanceAllowed(
                $isLoginAsCustomerEnabled ? IsAssistanceEnabled::ALLOWED : IsAssistanceEnabled::DENIED
            );
            $dataObject->setExtensionAttributes($extensionAttributes);
        }
        return $result;
    }
}

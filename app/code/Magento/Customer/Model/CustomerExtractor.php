<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model;

use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\GroupManagementInterface;
use Magento\Framework\App\RequestInterface;

/**
 * Class \Magento\Customer\Model\CustomerExtractor
 *
 * @since 2.0.0
 */
class CustomerExtractor
{
    /**
     * @var \Magento\Customer\Model\Metadata\FormFactory
     * @since 2.0.0
     */
    protected $formFactory;

    /**
     * @var \Magento\Customer\Api\Data\CustomerInterfaceFactory
     * @since 2.0.0
     */
    protected $customerFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     * @since 2.0.0
     */
    protected $storeManager;

    /**
     * @var GroupManagementInterface
     * @since 2.0.0
     */
    protected $customerGroupManagement;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     * @since 2.0.0
     */
    protected $dataObjectHelper;

    /**
     * @param Metadata\FormFactory $formFactory
     * @param \Magento\Customer\Api\Data\CustomerInterfaceFactory $customerFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param GroupManagementInterface $customerGroupManagement
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Customer\Model\Metadata\FormFactory $formFactory,
        \Magento\Customer\Api\Data\CustomerInterfaceFactory $customerFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        GroupManagementInterface $customerGroupManagement,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
    ) {
        $this->formFactory = $formFactory;
        $this->customerFactory = $customerFactory;
        $this->storeManager = $storeManager;
        $this->customerGroupManagement = $customerGroupManagement;
        $this->dataObjectHelper = $dataObjectHelper;
    }

    /**
     * @param string $formCode
     * @param RequestInterface $request
     * @param array $attributeValues
     * @return CustomerInterface
     * @since 2.0.0
     */
    public function extract(
        $formCode,
        RequestInterface $request,
        array $attributeValues = []
    ) {
        $customerForm = $this->formFactory->create(
            CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER,
            $formCode,
            $attributeValues
        );

        $customerData = $customerForm->extractData($request);
        $customerData = $customerForm->compactData($customerData);

        $allowedAttributes = $customerForm->getAllowedAttributes();
        $isGroupIdEmpty = isset($allowedAttributes['group_id']);

        $customerDataObject = $this->customerFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $customerDataObject,
            $customerData,
            \Magento\Customer\Api\Data\CustomerInterface::class
        );
        $store = $this->storeManager->getStore();
        if ($isGroupIdEmpty) {
            $customerDataObject->setGroupId(
                $this->customerGroupManagement->getDefaultGroup($store->getId())->getId()
            );
        }

        $customerDataObject->setWebsiteId($store->getWebsiteId());
        $customerDataObject->setStoreId($store->getId());

        return $customerDataObject;
    }
}

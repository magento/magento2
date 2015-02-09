<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model;

use Magento\Customer\Api\GroupManagementInterface;
use Magento\Framework\App\RequestInterface;

class CustomerExtractor
{
    /**
     * @var \Magento\Customer\Model\Metadata\FormFactory
     */
    protected $formFactory;

    /**
     * @var \Magento\Customer\Api\Data\CustomerDataBuilder
     */
    protected $customerBuilder;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var GroupManagementInterface
     */
    protected $customerGroupManagement;

    /**
     * @param Metadata\FormFactory $formFactory
     * @param \Magento\Customer\Api\Data\CustomerDataBuilder $customerBuilder
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param GroupManagementInterface $customerGroupManagement
     */
    public function __construct(
        \Magento\Customer\Model\Metadata\FormFactory $formFactory,
        \Magento\Customer\Api\Data\CustomerDataBuilder $customerBuilder,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        GroupManagementInterface $customerGroupManagement
    ) {
        $this->formFactory = $formFactory;
        $this->customerBuilder = $customerBuilder;
        $this->storeManager = $storeManager;
        $this->customerGroupManagement = $customerGroupManagement;
    }

    /**
     * @param string $formCode
     * @param RequestInterface $request
     * @return \Magento\Customer\Api\Data\CustomerInterface
     */
    public function extract($formCode, RequestInterface $request)
    {
        $customerForm = $this->formFactory->create('customer', $formCode);

        $allowedAttributes = $customerForm->getAllowedAttributes();
        $isGroupIdEmpty = true;
        $customerData = [];
        foreach ($allowedAttributes as $attribute) {
            $attributeCode = $attribute->getAttributeCode();
            if ($attributeCode == 'group_id') {
                $isGroupIdEmpty = false;
            }
            $customerData[$attributeCode] = $request->getParam($attributeCode);
        }
        $this->customerBuilder->populateWithArray($customerData);
        $store = $this->storeManager->getStore();
        if ($isGroupIdEmpty) {
            $this->customerBuilder->setGroupId(
                $this->customerGroupManagement->getDefaultGroup($store->getId())->getId()
            );
        }

        $this->customerBuilder->setWebsiteId($store->getWebsiteId());
        $this->customerBuilder->setStoreId($store->getId());

        return $this->customerBuilder->create();
    }
}

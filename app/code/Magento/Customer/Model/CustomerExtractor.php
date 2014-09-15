<?php
/**
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Customer\Model;

use Magento\Customer\Service\V1\CustomerGroupServiceInterface;
use Magento\Framework\App\RequestInterface;

class CustomerExtractor
{
    /**
     * @var \Magento\Customer\Model\Metadata\FormFactory
     */
    protected $formFactory;

    /**
     * @var \Magento\Customer\Service\V1\Data\CustomerBuilder
     */
    protected $customerBuilder;

    /**
     * @var \Magento\Framework\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var CustomerGroupServiceInterface
     */
    protected $groupService;

    /**
     * @param Metadata\FormFactory $formFactory
     * @param \Magento\Customer\Service\V1\Data\CustomerBuilder $customerBuilder
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     * @param CustomerGroupServiceInterface $groupService
     */
    public function __construct(
        \Magento\Customer\Model\Metadata\FormFactory $formFactory,
        \Magento\Customer\Service\V1\Data\CustomerBuilder $customerBuilder,
        \Magento\Framework\StoreManagerInterface $storeManager,
        CustomerGroupServiceInterface $groupService
    ) {
        $this->formFactory = $formFactory;
        $this->customerBuilder = $customerBuilder;
        $this->storeManager = $storeManager;
        $this->groupService = $groupService;
    }

    /**
     * @param string $formCode
     * @param RequestInterface $request
     * @return \Magento\Customer\Service\V1\Data\Customer
     */
    public function extract($formCode, RequestInterface $request)
    {
        $customerForm = $this->formFactory->create('customer', $formCode);

        $allowedAttributes = $customerForm->getAllowedAttributes();
        $isGroupIdEmpty = true;
        $customerData = array();
        foreach ($allowedAttributes as $attribute) {
            // confirmation in request param is the repeated password, not a confirmation code.
            if ($attribute === 'confirmation') {
                continue;
            }
            $attributeCode = $attribute->getAttributeCode();
            if ($attributeCode == 'group_id') {
                $isGroupIdEmpty = false;
            }
            $customerData[$attributeCode] = $request->getParam($attributeCode);
        }
        $this->customerBuilder->populateWithArray($customerData);
        $store = $this->storeManager->getStore();
        if ($isGroupIdEmpty) {
            $this->customerBuilder->setGroupId($this->groupService->getDefaultGroup($store->getId())->getId());
        }

        $this->customerBuilder->setWebsiteId($store->getWebsiteId());
        $this->customerBuilder->setStoreId($store->getId());

        return $this->customerBuilder->create();
    }
}

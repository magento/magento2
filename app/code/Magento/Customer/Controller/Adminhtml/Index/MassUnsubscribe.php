<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Controller\Adminhtml\Index;

use Magento\Backend\Model\View\Result\Redirect;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Config\Share;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Magento\Newsletter\Model\SubscriptionManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;
use Magento\Eav\Model\Entity\Collection\AbstractCollection;

/**
 * Class to mass unsubscribe customers by ids
 */
class MassUnsubscribe extends AbstractMassAction implements HttpPostActionInterface
{
    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var SubscriptionManagerInterface
     */
    private $subscriptionManager;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Share
     */
    private $shareConfig;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param CustomerRepositoryInterface $customerRepository
     * @param SubscriptionManagerInterface $subscriptionManager
     * @param StoreManagerInterface $storeManager
     * @param Share $shareConfig
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        CustomerRepositoryInterface $customerRepository,
        SubscriptionManagerInterface $subscriptionManager,
        StoreManagerInterface $storeManager,
        Share $shareConfig
    ) {
        parent::__construct($context, $filter, $collectionFactory);
        $this->customerRepository = $customerRepository;
        $this->subscriptionManager = $subscriptionManager;
        $this->storeManager = $storeManager;
        $this->shareConfig = $shareConfig;
    }

    /**
     * Customer mass unsubscribe action
     *
     * @param AbstractCollection $collection
     * @return Redirect
     */
    protected function massAction(AbstractCollection $collection)
    {
        $customersUpdated = 0;
        foreach ($collection->getAllIds() as $customerId) {
            // Verify that customer exists
            $customer = $this->customerRepository->getById($customerId);
            foreach ($this->getUnsubscribeStoreIds($customer) as $storeId) {
                $this->subscriptionManager->unsubscribeCustomer((int)$customerId, $storeId);
            }
            $customersUpdated++;
        }

        if ($customersUpdated) {
            $this->messageManager->addSuccessMessage(__('A total of %1 record(s) were updated.', $customersUpdated));
        }
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath($this->getComponentRefererUrl());

        return $resultRedirect;
    }

    /**
     * Get store ids to unsubscribe customer
     *
     * @param CustomerInterface $customer
     * @return array
     */
    private function getUnsubscribeStoreIds(CustomerInterface $customer): array
    {
        $storeIds = [];
        if ($this->shareConfig->isGlobalScope()) {
            foreach ($this->storeManager->getStores() as $store) {
                $storeIds[(int)$store->getWebsiteId()] = (int)$store->getId();
            }
        } else {
            $storeIds = [(int)$customer->getStoreId()];
        }

        return $storeIds;
    }
}

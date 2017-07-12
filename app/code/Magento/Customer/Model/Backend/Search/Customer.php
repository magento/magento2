<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Model\Search;

use Magento\Backend\Api\Search\ItemsInterface;
use Magento\Backend\Model\Search\ItemsAbstract;

class Customer extends ItemsAbstract implements ItemsInterface
{
    /**
     * Adminhtml data
     *
     * @var \Magento\Backend\Helper\Data
     */
    protected $_adminhtmlData = null;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var \Magento\Framework\Api\FilterBuilder
     */
    protected $filterBuilder;

    /**
     * @var \Magento\Customer\Helper\View
     */
    protected $_customerViewHelper;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Backend\Helper\Data $adminhtmlData
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Framework\Api\FilterBuilder $filterBuilder
     * @param \Magento\Customer\Helper\View $customerViewHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Helper\Data $adminhtmlData,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Api\FilterBuilder $filterBuilder,
        \Magento\Customer\Helper\View $customerViewHelper,
        array $data = []
    ) {
        $this->_adminhtmlData = $adminhtmlData;
        $this->customerRepository = $customerRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->_customerViewHelper = $customerViewHelper;
        parent::__construct($data);
    }

    /**
     * {@inheritdoc}
     */
    public function getResults()
    {
        $result = [];
        if (!$this->hasData(self::START) || !$this->hasData(self::LIMIT) || !$this->hasData(self::QUERY)) {
            return $result;
        }

        $this->searchCriteriaBuilder->setCurrentPage($this->getData(self::START));
        $this->searchCriteriaBuilder->setPageSize($this->getData(self::LIMIT));
        $searchFields = ['firstname', 'lastname', 'company'];
        $filters = [];
        foreach ($searchFields as $field) {
            $filters[] = $this->filterBuilder
                ->setField($field)
                ->setConditionType('like')
                ->setValue($this->getData(self::QUERY) . '%')
                ->create();
        }
        $this->searchCriteriaBuilder->addFilters($filters);
        $searchCriteria = $this->searchCriteriaBuilder->create();
        $searchResults = $this->customerRepository->getList($searchCriteria);

        foreach ($searchResults->getItems() as $customer) {
            $customerAddresses = $customer->getAddresses();
            /** Look for a company name defined in default billing address */
            $company = null;
            foreach ($customerAddresses as $customerAddress) {
                if ($customerAddress->getId() == $customer->getDefaultBilling()) {
                    $company = $customerAddress->getCompany();
                    break;
                }
            }
            $result[] = [
                'id' => 'customer/1/' . $customer->getId(),
                'type' => __('Customer'),
                'name' => $this->_customerViewHelper->getCustomerName($customer),
                'description' => $company,
                'url' => $this->_adminhtmlData->getUrl('customer/index/edit', ['id' => $customer->getId()]),
            ];
        }
        return $result;
    }
}

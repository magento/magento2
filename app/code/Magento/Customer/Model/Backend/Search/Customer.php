<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\Backend\Search;

use Magento\Backend\Api\Search\ItemsInterface;
use Magento\Backend\Model\Search\SearchCriteria;

class Customer implements ItemsInterface
{
    /**
     * Adminhtml data
     *
     * @var \Magento\Backend\Helper\Data
     */
    protected $_adminHtmlData = null;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $criteriaBuilder;

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
     * @param \Magento\Backend\Helper\Data $adminHtmlData
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $criteriaBuilder
     * @param \Magento\Framework\Api\FilterBuilder $filterBuilder
     * @param \Magento\Customer\Helper\View $customerViewHelper
     */
    public function __construct(
        \Magento\Backend\Helper\Data $adminHtmlData,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $criteriaBuilder,
        \Magento\Framework\Api\FilterBuilder $filterBuilder,
        \Magento\Customer\Helper\View $customerViewHelper
    ) {
        $this->_adminHtmlData = $adminHtmlData;
        $this->customerRepository = $customerRepository;
        $this->criteriaBuilder = $criteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->_customerViewHelper = $customerViewHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function getResults(SearchCriteria $searchCriteria)
    {
        $result = [];
        if (!$searchCriteria->getStart() || !$searchCriteria->getLimit() || !$searchCriteria->getQuery()) {
            return $result;
        }

        $this->criteriaBuilder->setCurrentPage($searchCriteria->getStart());
        $this->criteriaBuilder->setPageSize($searchCriteria->getLimit());
        $searchFields = ['firstname', 'lastname', 'company', 'email'];
        $filters = [];
        foreach ($searchFields as $field) {
            $filters[] = $this->filterBuilder
                ->setField($field)
                ->setConditionType('like')
                ->setValue($searchCriteria->getQuery() . '%')
                ->create();
        }
        $this->criteriaBuilder->addFilters($filters);
        $searchCriteria = $this->criteriaBuilder->create();
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
                'url' => $this->_adminHtmlData->getUrl('customer/index/edit', ['id' => $customer->getId()]),
            ];
        }
        return $result;
    }
}

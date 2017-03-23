<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Model\Search;

/**
 * Search Customer Model
 *
 * @method Customer setQuery(string $query)
 * @method string|null getQuery()
 * @method bool hasQuery()
 * @method Customer setStart(int $startPosition)
 * @method int|null getStart()
 * @method bool hasStart()
 * @method Customer setLimit(int $limit)
 * @method int|null getLimit()
 * @method bool hasLimit()
 * @method Customer setResults(array $results)
 * @method array getResults()
 */
class Customer extends \Magento\Framework\DataObject
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
     */
    public function __construct(
        \Magento\Backend\Helper\Data $adminhtmlData,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Api\FilterBuilder $filterBuilder,
        \Magento\Customer\Helper\View $customerViewHelper
    ) {
        $this->_adminhtmlData = $adminhtmlData;
        $this->customerRepository = $customerRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->_customerViewHelper = $customerViewHelper;
    }

    /**
     * Load search results
     *
     * @return $this
     */
    public function load()
    {
        $result = [];
        if (!$this->hasStart() || !$this->hasLimit() || !$this->hasQuery()) {
            $this->setResults($result);
            return $this;
        }

        $this->searchCriteriaBuilder->setCurrentPage($this->getStart());
        $this->searchCriteriaBuilder->setPageSize($this->getLimit());
        $searchFields = ['firstname', 'lastname', 'company'];
        $filters = [];
        foreach ($searchFields as $field) {
            $filters[] = $this->filterBuilder
                ->setField($field)
                ->setConditionType('like')
                ->setValue($this->getQuery() . '%')
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
        $this->setResults($result);
        return $this;
    }
}

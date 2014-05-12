<?php
/**
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
class Customer extends \Magento\Framework\Object
{
    /**
     * Adminhtml data
     *
     * @var \Magento\Backend\Helper\Data
     */
    protected $_adminhtmlData = null;

    /**
     * @var \Magento\Customer\Service\V1\CustomerAccountService
     */
    protected $_customerService;

    /**
     * @var \Magento\Framework\Service\V1\Data\SearchCriteriaBuilder
     */
    protected $_searchCriteriaBuilder;

    /**
     * @var \Magento\Framework\Service\V1\Data\FilterBuilder
     */
    protected $_filterBuilder;

    /**
     * @var \Magento\Customer\Helper\View
     */
    protected $_customerViewHelper;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Backend\Helper\Data $adminhtmlData
     * @param \Magento\Customer\Service\V1\CustomerAccountService $customerService
     * @param \Magento\Framework\Service\V1\Data\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Framework\Service\V1\Data\FilterBuilder $filterBuilder
     * @param \Magento\Customer\Helper\View $customerViewHelper
     */
    public function __construct(
        \Magento\Backend\Helper\Data $adminhtmlData,
        \Magento\Customer\Service\V1\CustomerAccountService $customerService,
        \Magento\Framework\Service\V1\Data\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Service\V1\Data\FilterBuilder $filterBuilder,
        \Magento\Customer\Helper\View $customerViewHelper
    ) {
        $this->_adminhtmlData = $adminhtmlData;
        $this->_customerService = $customerService;
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->_filterBuilder = $filterBuilder;
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

        $this->_searchCriteriaBuilder->setCurrentPage($this->getStart());
        $this->_searchCriteriaBuilder->setPageSize($this->getLimit());
        $searchFields = ['firstname', 'lastname', 'company'];
        $filters = [];
        foreach ($searchFields as $field) {
            $filters[] = $this->_filterBuilder
                ->setField($field)
                ->setConditionType('like')
                ->setValue($this->getQuery() . '%')
                ->create();
        }
        $this->_searchCriteriaBuilder->addFilter($filters);
        $searchCriteria = $this->_searchCriteriaBuilder->create();
        $searchResults = $this->_customerService->searchCustomers($searchCriteria);

        /** @var \Magento\Customer\Service\V1\Data\CustomerDetails $customerDetails */
        foreach ($searchResults->getItems() as $customerDetails) {
            $customerData = $customerDetails->getCustomer();
            $customerAddresses = $customerDetails->getAddresses();
            /** Look for a company name defined in default billing address */
            $company = null;
            foreach ($customerAddresses as $customerAddress) {
                if ($customerAddress->isDefaultBilling()) {
                    $company = $customerAddress->getCompany();
                    break;
                }
            }
            $result[] = array(
                'id' => 'customer/1/' . $customerData->getId(),
                'type' => __('Customer'),
                'name' => $this->_customerViewHelper->getCustomerName($customerData),
                'description' => $company,
                'url' => $this->_adminhtmlData->getUrl('customer/index/edit', array('id' => $customerData->getId()))
            );
        }
        $this->setResults($result);
        return $this;
    }
}

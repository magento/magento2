<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Multishipping\Controller\Checkout\Address;

use Magento\Framework\App\Action\Context;
use Magento\Multishipping\Controller\Checkout\Address;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;

/**
 * Class ShippingSaved
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class ShippingSaved extends Address
{
    /**
     * @var \Magento\Customer\Api\AddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * @var \Magento\Framework\Api\FilterBuilder
     */
    private $filterBuilder;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * Initialize dependencies.
     *
     * @param Context $context
     * @param AddressRepositoryInterface $addressRepository
     * @param FilterBuilder $filterBuilder
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        Context $context,
        AddressRepositoryInterface $addressRepository,
        FilterBuilder $filterBuilder,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        parent::__construct($context);
        $this->addressRepository = $addressRepository;
        $this->filterBuilder = $filterBuilder;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @return void
     */
    public function execute()
    {
        $filter = $this->filterBuilder->setField('parent_id')->setValue($this->_getCheckout()->getCustomer()->getId())
            ->setConditionType('eq')->create();
        $addresses = (array)($this->addressRepository->getList(
            $this->searchCriteriaBuilder->addFilters([$filter])->create()
        )->getItems());

        /**
         * if we create first address we need reset emd init checkout
         */
        if (count($addresses) === 1) {
            $this->_getCheckout()->reset();
        }
        $this->_redirect('*/checkout/addresses');
    }
}

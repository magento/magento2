<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Multishipping\Block\Checkout\Address;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Customer\Helper\Address as CustomerAddressHelper;
use Magento\Customer\Api\AddressRepositoryInterface;

/**
 * Class Select
 * Multishipping checkout select billing address
 */
class Select extends \Magento\Multishipping\Block\Checkout\AbstractMultishipping
{
    /**
     * @var CustomerAddressHelper
     */
    protected $_customerAddressHelper;

    /**
     * @var \Magento\Customer\Model\Address\Mapper
     */
    protected $addressMapper;

    /**
     * @var bool
     */
    protected $_isScopePrivate = true;

    /**
     * @var \Magento\Framework\Api\FilterBuilder
     */
    protected $filterBuilder;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var AddressRepositoryInterface
     */
    protected $addressRepository;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Multishipping\Model\Checkout\Type\Multishipping $multishipping
     * @param CustomerAddressHelper $customerAddressHelper
     * @param \Magento\Customer\Model\Address\Mapper $addressMapper
     * @param AddressRepositoryInterface $addressRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Framework\Api\FilterBuilder $filterBuilder
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Multishipping\Model\Checkout\Type\Multishipping $multishipping,
        CustomerAddressHelper $customerAddressHelper,
        \Magento\Customer\Model\Address\Mapper $addressMapper,
        AddressRepositoryInterface $addressRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Api\FilterBuilder $filterBuilder,
        array $data = []
    ) {
        $this->_customerAddressHelper = $customerAddressHelper;
        $this->addressMapper = $addressMapper;
        $this->addressRepository = $addressRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        parent::__construct($context, $multishipping, $data);
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->pageConfig->getTitle()->set(
            __('Change Billing Address') . ' - ' . $this->pageConfig->getTitle()->getDefault()
        );
        return parent::_prepareLayout();
    }

    /**
     * Get a list of current customer addresses.
     *
     * @return \Magento\Customer\Api\Data\AddressInterface[]
     */
    public function getAddress()
    {
        $addresses = $this->getData('address_collection');
        if ($addresses === null) {
            try {
                $filter =  $this->filterBuilder->setField('parent_id')
                    ->setValue($this->_multishipping->getCustomer()->getId())
                    ->setConditionType('eq')
                    ->create();
                $addresses = (array)($this->addressRepository->getList(
                    $this->searchCriteriaBuilder->addFilters([$filter])->create()
                )->getItems());
            } catch (NoSuchEntityException $e) {
                return [];
            }
            $this->setData('address_collection', $addresses);
        }
        return $addresses;
    }

    /**
     * Represent customer address in HTML format.
     *
     * @param \Magento\Customer\Api\Data\AddressInterface $address
     * @return string
     */
    public function getAddressAsHtml(\Magento\Customer\Api\Data\AddressInterface $address)
    {
        $formatTypeRenderer = $this->_customerAddressHelper->getFormatTypeRenderer('html');
        $result = '';
        if ($formatTypeRenderer) {
            $result = $formatTypeRenderer->renderArray($this->addressMapper->toFlatArray($address));
        }
        return $result;
    }

    /**
     * Check if provided address is default customer billing address.
     *
     * @param \Magento\Customer\Api\Data\AddressInterface $address
     * @return bool
     */
    public function isAddressDefaultBilling(\Magento\Customer\Api\Data\AddressInterface $address)
    {
        return $address->getId() == $this->_multishipping->getCustomer()->getDefaultBilling();
    }

    /**
     * Check if provided address is default customer shipping address.
     *
     * @param \Magento\Customer\Api\Data\AddressInterface $address
     * @return bool
     */
    public function isAddressDefaultShipping(\Magento\Customer\Api\Data\AddressInterface $address)
    {
        return $address->getId() == $this->_multishipping->getCustomer()->getDefaultShipping();
    }

    /**
     * Get URL of customer address edit page.
     *
     * @param \Magento\Customer\Api\Data\AddressInterface $address
     * @return string
     */
    public function getEditAddressUrl(\Magento\Customer\Api\Data\AddressInterface $address)
    {
        return $this->getUrl('*/*/editAddress', ['id' => $address->getId()]);
    }

    /**
     * Get URL of page, at which customer billing address can be set.
     *
     * @param \Magento\Customer\Api\Data\AddressInterface $address
     * @return string
     */
    public function getSetAddressUrl(\Magento\Customer\Api\Data\AddressInterface $address)
    {
        return $this->getUrl('*/*/setBilling', ['id' => $address->getId()]);
    }

    /**
     * @return string
     */
    public function getAddNewUrl()
    {
        return $this->getUrl('*/*/newBilling');
    }

    /**
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('*/checkout/billing');
    }
}

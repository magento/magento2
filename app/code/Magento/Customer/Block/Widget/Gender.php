<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Block\Widget;

use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\OptionInterface;

/**
 * Block to render customer's gender attribute
 *
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 * @since 2.0.0
 */
class Gender extends AbstractWidget
{
    /**
     * @var \Magento\Customer\Model\Session
     * @since 2.0.0
     */
    protected $_customerSession;

    /**
     * @var CustomerRepositoryInterface
     * @since 2.0.0
     */
    protected $customerRepository;

    /**
     * Create an instance of the Gender widget
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Helper\Address $addressHelper
     * @param CustomerMetadataInterface $customerMetadata
     * @param CustomerRepositoryInterface $customerRepository
     * @param \Magento\Customer\Model\Session $customerSession
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Helper\Address $addressHelper,
        CustomerMetadataInterface $customerMetadata,
        CustomerRepositoryInterface $customerRepository,
        \Magento\Customer\Model\Session $customerSession,
        array $data = []
    ) {
        $this->_customerSession = $customerSession;
        $this->customerRepository = $customerRepository;
        parent::__construct($context, $addressHelper, $customerMetadata, $data);
        $this->_isScopePrivate = true;
    }

    /**
     * Initialize block
     *
     * @return void
     * @since 2.0.0
     */
    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('widget/gender.phtml');
    }

    /**
     * Check if gender attribute enabled in system
     * @return bool
     * @since 2.0.0
     */
    public function isEnabled()
    {
        return $this->_getAttribute('gender') ? (bool)$this->_getAttribute('gender')->isVisible() : false;
    }

    /**
     * Check if gender attribute marked as required
     * @return bool
     * @since 2.0.0
     */
    public function isRequired()
    {
        return $this->_getAttribute('gender') ? (bool)$this->_getAttribute('gender')->isRequired() : false;
    }

    /**
     * Get current customer from session
     *
     * @return CustomerInterface
     * @since 2.0.0
     */
    public function getCustomer()
    {
        return $this->customerRepository->getById($this->_customerSession->getCustomerId());
    }

    /**
     * Returns options from gender attribute
     * @return OptionInterface[]
     * @since 2.0.0
     */
    public function getGenderOptions()
    {
        return $this->_getAttribute('gender')->getOptions();
    }
}

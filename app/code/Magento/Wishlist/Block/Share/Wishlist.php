<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Wishlist block shared items
 */
namespace Magento\Wishlist\Block\Share;

/**
 * @api
 * @since 100.0.2
 */
class Wishlist extends \Magento\Wishlist\Block\AbstractBlock
{
    /**
     * Customer instance
     *
     * @var \Magento\Customer\Api\Data\CustomerInterface
     */
    protected $_customer = null;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        array $data = []
    ) {
        $this->customerRepository = $customerRepository;
        parent::__construct(
            $context,
            $httpContext,
            $data
        );
    }

    /**
     * Prepare global layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->pageConfig->getTitle()->set($this->getHeader());
        return $this;
    }

    /**
     * Retrieve Shared Wishlist Customer instance
     *
     * @return \Magento\Customer\Api\Data\CustomerInterface
     */
    public function getWishlistCustomer()
    {
        if ($this->_customer === null) {
            $this->_customer = $this->customerRepository->getById($this->_getWishlist()->getCustomerId());
        }

        return $this->_customer;
    }

    /**
     * Retrieve Page Header
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeader()
    {
        return __("%1's Wish List", $this->escapeHtml($this->getWishlistCustomer()->getFirstname()));
    }
}

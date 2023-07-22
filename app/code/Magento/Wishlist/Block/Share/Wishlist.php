<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Wishlist block shared items
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Wishlist\Block\Share;

use Magento\Catalog\Block\Product\Context as ProductContext;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\App\Http\Context;
use Magento\Framework\Phrase;
use Magento\Wishlist\Block\AbstractBlock;

/**
 * @api
 * @since 100.0.2
 */
class Wishlist extends AbstractBlock
{
    /**
     * Customer instance
     *
     * @var CustomerInterface
     */
    protected $_customer = null;

    /**
     * @param ProductContext $context
     * @param Context $httpContext
     * @param CustomerRepositoryInterface $customerRepository
     * @param array $data
     */
    public function __construct(
        ProductContext $context,
        Context $httpContext,
        private readonly CustomerRepositoryInterface $customerRepository,
        array $data = []
    ) {
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
     *
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
     * @return CustomerInterface
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
     * @return Phrase
     */
    public function getHeader()
    {
        return __("%1's Wish List", $this->escapeHtml($this->getWishlistCustomer()->getFirstname()));
    }
}

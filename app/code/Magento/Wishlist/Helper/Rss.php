<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Wishlist\Helper;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Helper\View;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Data\Helper\PostHelper;
use Magento\Framework\Registry;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Wishlist\Controller\WishlistProviderInterface;
use Magento\Wishlist\Model\Wishlist;
use Magento\Wishlist\Model\WishlistFactory;

/**
 * Wishlist rss helper
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 *
 * @api
 * @since 100.0.2
 */
class Rss extends Data
{
    /**
     * @var CustomerInterface
     */
    protected $_customer;

    /**
     * @var CustomerInterfaceFactory
     */
    protected $_customerFactory;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $_customerRepository;

    /**
     * @param Context $context
     * @param Registry $coreRegistry
     * @param Session $customerSession
     * @param WishlistFactory $wishlistFactory
     * @param StoreManagerInterface $storeManager
     * @param PostHelper $postDataHelper
     * @param View $customerViewHelper
     * @param WishlistProviderInterface $wishlistProvider
     * @param ProductRepositoryInterface $productRepository
     * @param CustomerInterfaceFactory $customerFactory
     * @param CustomerRepositoryInterface $customerRepository
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        Session $customerSession,
        WishlistFactory $wishlistFactory,
        StoreManagerInterface $storeManager,
        PostHelper $postDataHelper,
        View $customerViewHelper,
        WishlistProviderInterface $wishlistProvider,
        ProductRepositoryInterface $productRepository,
        CustomerInterfaceFactory $customerFactory,
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->_customerFactory = $customerFactory;
        $this->_customerRepository = $customerRepository;

        parent::__construct(
            $context,
            $coreRegistry,
            $customerSession,
            $wishlistFactory,
            $storeManager,
            $postDataHelper,
            $customerViewHelper,
            $wishlistProvider,
            $productRepository
        );
    }

    /**
     * Retrieve Wishlist model
     *
     * @return Wishlist
     */
    public function getWishlist()
    {
        if ($this->_wishlist === null) {
            $this->_wishlist = $this->_wishlistFactory->create();

            $wishlistId = $this->_getRequest()->getParam('wishlist_id');
            if ($wishlistId) {
                $this->_wishlist->load($wishlistId);
            } else {
                if ($this->getCustomer()->getId()) {
                    $this->_wishlist->loadByCustomerId($this->getCustomer()->getId());
                }
            }
        }
        return $this->_wishlist;
    }

    /**
     * Retrieve Customer instance
     *
     * @return CustomerInterface
     */
    public function getCustomer()
    {
        if ($this->_customer === null) {
            $params = $this->urlDecoder->decode($this->_getRequest()->getParam('data', ''));
            $data = explode(',', $params);
            $customerId = abs((int)$data[0]);
            if ($customerId && ($customerId == $this->_customerSession->getCustomerId())) {
                $this->_customer = $this->_customerRepository->getById($customerId);
            } else {
                $this->_customer = $this->_customerFactory->create();
            }
        }

        return $this->_customer;
    }

    /**
     * Is allow RSS
     *
     * @return bool
     */
    public function isRssAllow()
    {
        return $this->_moduleManager->isEnabled('Magento_Rss')
            && $this->scopeConfig->isSetFlag(
                'rss/wishlist/active',
                ScopeInterface::SCOPE_STORE
            );
    }
}

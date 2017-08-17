<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Wishlist\Helper;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 *
 * @api
 */
class Rss extends \Magento\Wishlist\Helper\Data
{
    /**
     * @var \Magento\Customer\Api\Data\CustomerInterface
     */
    protected $_customer;

    /**
     * @var \Magento\Customer\Api\Data\CustomerInterfaceFactory
     */
    protected $_customerFactory;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $_customerRepository;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Wishlist\Model\WishlistFactory $wishlistFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Data\Helper\PostHelper $postDataHelper
     * @param \Magento\Customer\Helper\View $customerViewHelper
     * @param \Magento\Wishlist\Controller\WishlistProviderInterface $wishlistProvider
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Customer\Api\Data\CustomerInterfaceFactory $customerFactory
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Wishlist\Model\WishlistFactory $wishlistFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Data\Helper\PostHelper $postDataHelper,
        \Magento\Customer\Helper\View $customerViewHelper,
        \Magento\Wishlist\Controller\WishlistProviderInterface $wishlistProvider,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Customer\Api\Data\CustomerInterfaceFactory $customerFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
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
     * @return \Magento\Wishlist\Model\Wishlist
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
     * @return \Magento\Customer\Api\Data\CustomerInterface
     */
    public function getCustomer()
    {
        if ($this->_customer === null) {
            $params = $this->urlDecoder->decode($this->_getRequest()->getParam('data'));
            $data   = explode(',', $params);
            $customerId    = abs(intval($data[0]));
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
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
    }
}

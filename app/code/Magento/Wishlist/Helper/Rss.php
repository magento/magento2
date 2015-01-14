<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Wishlist\Helper;

class Rss extends \Magento\Wishlist\Helper\Data
{
    /**
     * @var \Magento\Customer\Api\Data\CustomerInterface
     */
    protected $_customer;

    /**
     * @var \Magento\Customer\Api\Data\CustomerDataBuilder
     */
    protected $_customerBuilder;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $_customerRepository;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Wishlist\Model\WishlistFactory $wishlistFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Core\Helper\PostData $postDataHelper
     * @param \Magento\Customer\Helper\View $customerViewHelper
     * @param \Magento\Wishlist\Controller\WishlistProviderInterface $wishlistProvider
     * @param \Magento\Customer\Api\Data\CustomerDataBuilder $customerBuilder
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Wishlist\Model\WishlistFactory $wishlistFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Core\Helper\PostData $postDataHelper,
        \Magento\Customer\Helper\View $customerViewHelper,
        \Magento\Wishlist\Controller\WishlistProviderInterface $wishlistProvider,
        \Magento\Customer\Api\Data\CustomerDataBuilder $customerBuilder,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
    ) {
        $this->_customerBuilder = $customerBuilder;
        $this->_customerRepository = $customerRepository;

        parent::__construct(
            $context,
            $coreRegistry,
            $scopeConfig,
            $customerSession,
            $wishlistFactory,
            $storeManager,
            $postDataHelper,
            $customerViewHelper,
            $wishlistProvider
        );
    }

    /**
     * Retrieve Wishlist model
     *
     * @return \Magento\Wishlist\Model\Wishlist
     */
    public function getWishlist()
    {
        if (is_null($this->_wishlist)) {
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
        if (is_null($this->_customer)) {
            $params = $this->urlDecoder->decode($this->_getRequest()->getParam('data'));
            $data   = explode(',', $params);
            $customerId    = abs(intval($data[0]));
            if ($customerId && ($customerId == $this->_customerSession->getCustomerId())) {
                $this->_customer = $this->_customerRepository->getById($customerId);
            } else {
                $this->_customer = $this->_customerBuilder->create();
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
            && $this->_scopeConfig->isSetFlag('rss/wishlist/active', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
}

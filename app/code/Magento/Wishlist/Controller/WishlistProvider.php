<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Wishlist\Controller;

use Magento\Framework\App\RequestInterface;

/**
 * Class \Magento\Wishlist\Controller\WishlistProvider
 *
 * @since 2.0.0
 */
class WishlistProvider implements WishlistProviderInterface
{
    /**
     * @var \Magento\Wishlist\Model\Wishlist
     * @since 2.0.0
     */
    protected $wishlist;

    /**
     * @var \Magento\Wishlist\Model\WishlistFactory
     * @since 2.0.0
     */
    protected $wishlistFactory;

    /**
     * @var \Magento\Customer\Model\Session
     * @since 2.0.0
     */
    protected $customerSession;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     * @since 2.0.0
     */
    protected $messageManager;

    /**
     * @var \Magento\Framework\App\RequestInterface
     * @since 2.0.0
     */
    protected $request;

    /**
     * @param \Magento\Wishlist\Model\WishlistFactory $wishlistFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param RequestInterface $request
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Wishlist\Model\WishlistFactory $wishlistFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        RequestInterface $request
    ) {
        $this->request = $request;
        $this->wishlistFactory = $wishlistFactory;
        $this->customerSession = $customerSession;
        $this->messageManager = $messageManager;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @since 2.0.0
     */
    public function getWishlist($wishlistId = null)
    {
        if ($this->wishlist) {
            return $this->wishlist;
        }
        try {
            if (!$wishlistId) {
                $wishlistId = $this->request->getParam('wishlist_id');
            }
            $customerId = $this->customerSession->getCustomerId();
            $wishlist = $this->wishlistFactory->create();

            if (!$wishlistId && !$customerId) {
                return $wishlist;
            }

            if ($wishlistId) {
                $wishlist->load($wishlistId);
            } elseif ($customerId) {
                $wishlist->loadByCustomerId($customerId, true);
            }

            if (!$wishlist->getId() || $wishlist->getCustomerId() != $customerId) {
                throw new \Magento\Framework\Exception\NoSuchEntityException(
                    __('The requested Wish List doesn\'t exist.')
                );
            }
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $this->messageManager->addError($e->getMessage());
            return false;
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('We can\'t create the Wish List right now.'));
            return false;
        }
        $this->wishlist = $wishlist;
        return $wishlist;
    }
}

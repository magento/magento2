<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Wishlist\Controller;

use Exception;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Wishlist\Model\Wishlist;
use Magento\Wishlist\Model\WishlistFactory;

/**
 * WishlistProvider Controller
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class WishlistProvider implements WishlistProviderInterface
{
    /**
     * @var Wishlist
     */
    protected $wishlist;

    /**
     * @param WishlistFactory $wishlistFactory
     * @param Session $customerSession
     * @param ManagerInterface $messageManager
     * @param RequestInterface $request
     */
    public function __construct(
        protected readonly WishlistFactory $wishlistFactory,
        protected readonly Session $customerSession,
        protected readonly ManagerInterface $messageManager,
        protected readonly RequestInterface $request
    ) {
    }

    /**
     * @inheritdoc
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
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
                throw new NoSuchEntityException(
                    __('The requested Wish List doesn\'t exist.')
                );
            }
        } catch (NoSuchEntityException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return false;
        } catch (Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('We can\'t create the Wish List right now.'));
            return false;
        }
        $this->wishlist = $wishlist;
        return $wishlist;
    }
}

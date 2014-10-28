<?php
/**
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Wishlist\Controller\Shared;

use Magento\Wishlist\Controller\WishlistProviderInterface;

class WishlistProvider implements WishlistProviderInterface
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Magento\Wishlist\Model\WishlistFactory
     */
    protected $wishlistFactory;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Wishlist\Model\Wishlist
     */
    protected $wishlist;

    /**
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Wishlist\Model\WishlistFactory $wishlistFactory
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Wishlist\Model\WishlistFactory $wishlistFactory,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->request = $request;
        $this->wishlistFactory = $wishlistFactory;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * Retrieve current wishlist
     * @param string $wishlistId
     * @return \Magento\Wishlist\Model\Wishlist
     */
    public function getWishlist($wishlistId = null)
    {
        if ($this->wishlist) {
            return $this->wishlist;
        }
        $code = (string)$this->request->getParam('code');
        if (empty($code)) {
            return false;
        }

        $wishlist = $this->wishlistFactory->create()->loadByCode($code);
        if (!$wishlist->getId()) {
            return false;
        }

        $this->checkoutSession->setSharedWishlist($code);
        $this->wishlist = $wishlist;
        return $wishlist;
    }
}

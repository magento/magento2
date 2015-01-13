<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Wishlist\Controller\Index;

use Magento\Framework\App\Action;
use Magento\Framework\App\Action\NotFoundException;
use Magento\Wishlist\Controller\IndexInterface;

class Remove extends Action\Action implements IndexInterface
{
    /**
     * @var \Magento\Wishlist\Controller\WishlistProviderInterface
     */
    protected $wishlistProvider;

    /**
     * @param Action\Context $context
     * @param \Magento\Wishlist\Controller\WishlistProviderInterface $wishlistProvider
     */
    public function __construct(
        Action\Context $context,
        \Magento\Wishlist\Controller\WishlistProviderInterface $wishlistProvider
    ) {
        $this->wishlistProvider = $wishlistProvider;
        parent::__construct($context);
    }

    /**
     * Remove item
     *
     * @return void
     * @throws NotFoundException
     */
    public function execute()
    {
        $id = (int)$this->getRequest()->getParam('item');
        $item = $this->_objectManager->create('Magento\Wishlist\Model\Item')->load($id);
        if (!$item->getId()) {
            throw new NotFoundException();
        }
        $wishlist = $this->wishlistProvider->getWishlist($item->getWishlistId());
        if (!$wishlist) {
            throw new NotFoundException();
        }
        try {
            $item->delete();
            $wishlist->save();
        } catch (\Magento\Framework\Model\Exception $e) {
            $this->messageManager->addError(
                __('An error occurred while deleting the item from wish list: %1', $e->getMessage())
            );
        } catch (\Exception $e) {
            $this->messageManager->addError(__('An error occurred while deleting the item from wish list.'));
        }

        $this->_objectManager->get('Magento\Wishlist\Helper\Data')->calculate();
        $request = $this->getRequest();
        $refererUrl = (string)$request->getServer('HTTP_REFERER');
        $url = (string)$request->getParam(\Magento\Framework\App\Response\RedirectInterface::PARAM_NAME_REFERER_URL);
        if ($url) {
            $refererUrl = $url;
        }
        if ($request->getParam(\Magento\Framework\App\Action\Action::PARAM_NAME_URL_ENCODED) && $refererUrl) {
            $redirectUrl = $refererUrl;
        } else {
            $redirectUrl = $this->_redirect->getRedirectUrl($this->_url->getUrl('*/*'));
        }
        $this->getResponse()->setRedirect($redirectUrl);
    }
}

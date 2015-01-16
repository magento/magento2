<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Wishlist\Controller\Index;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Action;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Wishlist\Controller\IndexInterface;

class UpdateItemOptions extends Action\Action implements IndexInterface
{
    /**
     * @var \Magento\Wishlist\Controller\WishlistProviderInterface
     */
    protected $wishlistProvider;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @param Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Wishlist\Controller\WishlistProviderInterface $wishlistProvider
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Wishlist\Controller\WishlistProviderInterface $wishlistProvider,
        ProductRepositoryInterface $productRepository
    ) {
        $this->_customerSession = $customerSession;
        $this->wishlistProvider = $wishlistProvider;
        parent::__construct($context);
        $this->productRepository = $productRepository;
    }

    /**
     * Action to accept new configuration for a wishlist item
     *
     * @return void
     */
    public function execute()
    {
        $productId = (int)$this->getRequest()->getParam('product');
        if (!$productId) {
            $this->_redirect('*/');
            return;
        }

        try {
            $product = $this->productRepository->getById($productId);
        } catch (NoSuchEntityException $e) {
            $product = null;
        }

        if (!$product || !$product->isVisibleInCatalog()) {
            $this->messageManager->addError(__('We can\'t specify a product.'));
            $this->_redirect('*/');
            return;
        }

        try {
            $id = (int)$this->getRequest()->getParam('id');
            /* @var \Magento\Wishlist\Model\Item */
            $item = $this->_objectManager->create('Magento\Wishlist\Model\Item');
            $item->load($id);
            $wishlist = $this->wishlistProvider->getWishlist($item->getWishlistId());
            if (!$wishlist) {
                $this->_redirect('*/');
                return;
            }

            $buyRequest = new \Magento\Framework\Object($this->getRequest()->getParams());

            $wishlist->updateItem($id, $buyRequest)->save();

            $this->_objectManager->get('Magento\Wishlist\Helper\Data')->calculate();
            $this->_eventManager->dispatch(
                'wishlist_update_item',
                ['wishlist' => $wishlist, 'product' => $product, 'item' => $wishlist->getItem($id)]
            );

            $this->_objectManager->get('Magento\Wishlist\Helper\Data')->calculate();

            $message = __('%1 has been updated in your wish list.', $product->getName());
            $this->messageManager->addSuccess($message);
        } catch (\Magento\Framework\Model\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addError(__('An error occurred while updating wish list.'));
            $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
        }
        $this->_redirect('*/*', ['wishlist_id' => $wishlist->getId()]);
    }
}

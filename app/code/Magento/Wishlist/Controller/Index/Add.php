<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Wishlist\Controller\Index;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Action;
use Magento\Framework\App\Action\NotFoundException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Wishlist\Controller\IndexInterface;

class Add extends Action\Action implements IndexInterface
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
     * Adding new item
     *
     * @return void
     * @throws NotFoundException
     */
    public function execute()
    {
        $wishlist = $this->wishlistProvider->getWishlist();
        if (!$wishlist) {
            throw new NotFoundException();
        }

        $session = $this->_customerSession;

        $requestParams = $this->getRequest()->getParams();

        if ($session->getBeforeWishlistRequest()) {
            $requestParams = $session->getBeforeWishlistRequest();
            $session->unsBeforeWishlistRequest();
        }

        $productId = isset($requestParams['product']) ? (int)$requestParams['product'] : null;

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
            $buyRequest = new \Magento\Framework\Object($requestParams);

            $result = $wishlist->addNewItem($product, $buyRequest);
            if (is_string($result)) {
                throw new \Magento\Framework\Model\Exception($result);
            }
            $wishlist->save();

            $this->_eventManager->dispatch(
                'wishlist_add_product',
                ['wishlist' => $wishlist, 'product' => $product, 'item' => $result]
            );

            $referer = $session->getBeforeWishlistUrl();
            if ($referer) {
                $session->setBeforeWishlistUrl(null);
            } else {
                $referer = $this->_redirect->getRefererUrl();
            }

            /**
             *  Set referer to avoid referring to the compare popup window
             */
            $session->setAddActionReferer($referer);

            /** @var $helper \Magento\Wishlist\Helper\Data */
            $helper = $this->_objectManager->get('Magento\Wishlist\Helper\Data')->calculate();
            $message = __(
                '%1 has been added to your wishlist. Click <a href="%2">here</a> to continue shopping.',
                $this->_objectManager->get('Magento\Framework\Escaper')->escapeHtml($product->getName()),
                $this->_objectManager->get('Magento\Framework\Escaper')->escapeUrl($referer)
            );
            $this->messageManager->addSuccess($message);
        } catch (\Magento\Framework\Model\Exception $e) {
            $this->messageManager->addError(
                __('An error occurred while adding item to wish list: %1', $e->getMessage())
            );
        } catch (\Exception $e) {
            $this->messageManager->addError(__('An error occurred while adding item to wish list.'));
            $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
        }

        $this->_redirect('*', ['wishlist_id' => $wishlist->getId()]);
    }
}

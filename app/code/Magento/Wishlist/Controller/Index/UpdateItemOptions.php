<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Wishlist\Controller\Index;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Controller\ResultFactory;
use Magento\Wishlist\Controller\AbstractIndex;
use Magento\Wishlist\Controller\WishlistProviderInterface;
use Magento\Wishlist\Helper\Data;
use Magento\Wishlist\Model\Item;
use Psr\Log\LoggerInterface;

/**
 * Wishlist UpdateItemOptions Controller
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UpdateItemOptions extends AbstractIndex implements Action\HttpPostActionInterface
{
    /**
     * @var Session
     */
    protected $_customerSession;

    /**
     * @param Action\Context $context
     * @param Session $customerSession
     * @param WishlistProviderInterface $wishlistProvider
     * @param ProductRepositoryInterface $productRepository
     * @param Validator $formKeyValidator
     */
    public function __construct(
        Action\Context $context,
        Session $customerSession,
        protected readonly WishlistProviderInterface $wishlistProvider,
        protected readonly ProductRepositoryInterface $productRepository,
        protected readonly Validator $formKeyValidator
    ) {
        $this->_customerSession = $customerSession;
        parent::__construct($context);
    }

    /**
     * Action to accept new configuration for a wishlist item
     *
     * @return Redirect
     */
    public function execute()
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        if (!$this->formKeyValidator->validate($this->getRequest())) {
            return $resultRedirect->setPath('*/*/');
        }

        $productId = (int)$this->getRequest()->getParam('product');
        if (!$productId) {
            $resultRedirect->setPath('*/');
            return $resultRedirect;
        }

        try {
            $product = $this->productRepository->getById($productId);
        } catch (NoSuchEntityException $e) {
            $product = null;
        }

        if (!$product || !$product->isVisibleInCatalog()) {
            $this->messageManager->addErrorMessage(__('We can\'t specify a product.'));
            $resultRedirect->setPath('*/');
            return $resultRedirect;
        }

        try {
            $id = (int)$this->getRequest()->getParam('id');
            /* @var Item */
            $item = $this->_objectManager->create(Item::class);
            $item->load($id);
            $wishlist = $this->wishlistProvider->getWishlist($item->getWishlistId());
            if (!$wishlist) {
                $resultRedirect->setPath('*/');
                return $resultRedirect;
            }

            $buyRequest = new DataObject($this->getRequest()->getParams());

            $wishlist->updateItem($id, $buyRequest)->save();

            $this->_objectManager->get(Data::class)->calculate();
            $this->_eventManager->dispatch(
                'wishlist_update_item',
                ['wishlist' => $wishlist, 'product' => $product, 'item' => $wishlist->getItem($id)]
            );

            $this->_objectManager->get(Data::class)->calculate();

            $message = __('%1 has been updated in your Wish List.', $product->getName());
            $this->messageManager->addSuccessMessage($message);
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('We can\'t update your Wish List right now.'));
            $this->_objectManager->get(LoggerInterface::class)->critical($e);
        }
        $resultRedirect->setPath('*/*', ['wishlist_id' => $wishlist->getId()]);
        return $resultRedirect;
    }
}

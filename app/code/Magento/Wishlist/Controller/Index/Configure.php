<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Wishlist\Controller\Index;

use Magento\Catalog\Helper\Product\View;
use Magento\Framework\App\Action;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Magento\Wishlist\Controller\AbstractIndex;
use Magento\Wishlist\Controller\WishlistProviderInterface;
use Magento\Wishlist\Helper\Data;
use Magento\Wishlist\Model\Item;
use Psr\Log\LoggerInterface;

/**
 * Wishlist Configure Controller
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Configure extends AbstractIndex implements Action\HttpGetActionInterface
{
    /**
     * Core registry
     *
     * @var Registry
     */
    protected $_coreRegistry;

    /**
     * @param Action\Context $context
     * @param WishlistProviderInterface $wishlistProvider
     * @param Registry $coreRegistry
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Action\Context $context,
        protected readonly WishlistProviderInterface $wishlistProvider,
        Registry $coreRegistry,
        protected readonly PageFactory $resultPageFactory
    ) {
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context);
    }

    /**
     * Action to reconfigure wishlist item
     *
     * @return ResultInterface
     * @throws NotFoundException
     */
    public function execute()
    {
        $id = (int)$this->getRequest()->getParam('id');
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        try {
            /* @var $item Item */
            $item = $this->_objectManager->create(Item::class);
            $item->loadWithOptions($id);
            if (!$item->getId()) {
                throw new LocalizedException(
                    __("The Wish List item can't load at this time. Please try again later.")
                );
            }
            $wishlist = $this->wishlistProvider->getWishlist($item->getWishlistId());
            if (!$wishlist) {
                throw new NotFoundException(__('Page not found.'));
            }

            $this->_coreRegistry->register('wishlist_item', $item);

            $params = new DataObject();
            $params->setCategoryId(false);
            $params->setConfigureMode(true);
            $buyRequest = $item->getBuyRequest();
            if (!$buyRequest->getQty() && $item->getQty()) {
                $buyRequest->setQty($item->getQty());
            }
            if ($buyRequest->getQty() && !$item->getQty()) {
                $item->setQty($buyRequest->getQty());
                $this->_objectManager->get(Data::class)->calculate();
            }
            $params->setBuyRequest($buyRequest);
            /** @var Page $resultPage */
            $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
            $this->_objectManager->get(
                View::class
            )->prepareAndRender(
                $resultPage,
                $item->getProductId(),
                $this,
                $params
            );

            return $resultPage;
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $resultRedirect->setPath('*');
            return $resultRedirect;
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('We can\'t configure the product right now.'));
            $this->_objectManager->get(LoggerInterface::class)->critical($e);
            $resultRedirect->setPath('*');
            return $resultRedirect;
        }
    }
}

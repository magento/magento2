<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Wishlist\Controller\Index;

use Magento\Framework\App\Action;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Controller\ResultFactory;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 2.0.0
 */
class Configure extends \Magento\Wishlist\Controller\AbstractIndex
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     * @since 2.0.0
     */
    protected $_coreRegistry;

    /**
     * @var \Magento\Wishlist\Controller\WishlistProviderInterface
     * @since 2.0.0
     */
    protected $wishlistProvider;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     * @since 2.0.0
     */
    protected $resultPageFactory;

    /**
     * @param Action\Context $context
     * @param \Magento\Wishlist\Controller\WishlistProviderInterface $wishlistProvider
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @since 2.0.0
     */
    public function __construct(
        Action\Context $context,
        \Magento\Wishlist\Controller\WishlistProviderInterface $wishlistProvider,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        $this->wishlistProvider = $wishlistProvider;
        $this->_coreRegistry = $coreRegistry;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * Action to reconfigure wishlist item
     *
     * @return \Magento\Framework\Controller\ResultInterface
     * @throws NotFoundException
     * @since 2.0.0
     */
    public function execute()
    {
        $id = (int)$this->getRequest()->getParam('id');
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        try {
            /* @var $item \Magento\Wishlist\Model\Item */
            $item = $this->_objectManager->create(\Magento\Wishlist\Model\Item::class);
            $item->loadWithOptions($id);
            if (!$item->getId()) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('We can\'t load the Wish List item right now.')
                );
            }
            $wishlist = $this->wishlistProvider->getWishlist($item->getWishlistId());
            if (!$wishlist) {
                throw new NotFoundException(__('Page not found.'));
            }

            $this->_coreRegistry->register('wishlist_item', $item);

            $params = new \Magento\Framework\DataObject();
            $params->setCategoryId(false);
            $params->setConfigureMode(true);
            $buyRequest = $item->getBuyRequest();
            if (!$buyRequest->getQty() && $item->getQty()) {
                $buyRequest->setQty($item->getQty());
            }
            if ($buyRequest->getQty() && !$item->getQty()) {
                $item->setQty($buyRequest->getQty());
                $this->_objectManager->get(\Magento\Wishlist\Helper\Data::class)->calculate();
            }
            $params->setBuyRequest($buyRequest);
            /** @var \Magento\Framework\View\Result\Page $resultPage */
            $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
            $this->_objectManager->get(
                \Magento\Catalog\Helper\Product\View::class
            )->prepareAndRender(
                $resultPage,
                $item->getProductId(),
                $this,
                $params
            );

            return $resultPage;
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
            $resultRedirect->setPath('*');
            return $resultRedirect;
        } catch (\Exception $e) {
            $this->messageManager->addError(__('We can\'t configure the product right now.'));
            $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
            $resultRedirect->setPath('*');
            return $resultRedirect;
        }
    }
}

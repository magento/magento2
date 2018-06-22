<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Wishlist\Controller\Index;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Framework\App\Action;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Controller\ResultFactory;
use Magento\Wishlist\Controller\WishlistProviderInterface;
use Magento\Wishlist\Model\Item;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Remove extends \Magento\Wishlist\Controller\AbstractIndex
{
    /**
     * @var WishlistProviderInterface
     */
    protected $wishlistProvider;

    /**
     * @var Validator
     */
    protected $formKeyValidator;

    /**
     * @var ProductCollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @param Action\Context $context
     * @param WishlistProviderInterface $wishlistProvider
     * @param Validator $formKeyValidator
     * @param ProductCollectionFactory|null $productCollectionFactory
     */
    public function __construct(
        Action\Context $context,
        WishlistProviderInterface $wishlistProvider,
        Validator $formKeyValidator,
        ProductCollectionFactory $productCollectionFactory = null
    ) {
        $this->wishlistProvider = $wishlistProvider;
        $this->formKeyValidator = $formKeyValidator;
        $this->productCollectionFactory = $productCollectionFactory
            ?: \Magento\Framework\App\ObjectManager::getInstance()->get(ProductCollectionFactory::class);
        parent::__construct($context);
    }

    /**
     * Remove item
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     * @throws NotFoundException
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        if (!$this->formKeyValidator->validate($this->getRequest())) {
            return $resultRedirect->setPath('*/*/');
        }

        $id = (int)$this->getRequest()->getParam('item');
        /** @var Item $item */
        $item = $this->_objectManager->create(Item::class)->load($id);
        if (!$item->getId()) {
            throw new NotFoundException(__('Page not found.'));
        }
        $wishlist = $this->wishlistProvider->getWishlist($item->getWishlistId());
        if (!$wishlist) {
            throw new NotFoundException(__('Page not found.'));
        }
        try {
            $item->delete();
            $wishlist->save();
            $product = $this->productCollectionFactory
                ->create()
                ->addIdFilter($item->getProductId())
                ->addAttributeToSelect('name')
                ->getFirstItem();
            $this->messageManager->addComplexSuccessMessage(
                'removeWishlistItemSuccessMessage',
                [
                    'product_name' => $product->getName()
                ]
            );
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError(
                __('We can\'t delete the item from Wish List right now because of an error: %1.', $e->getMessage())
            );
        } catch (\Exception $e) {
            $this->messageManager->addError(__('We can\'t delete the item from the Wish List right now.'));
        }

        $this->_objectManager->get(\Magento\Wishlist\Helper\Data::class)->calculate();
        $refererUrl = $this->_redirect->getRefererUrl();
        if ($refererUrl) {
            $redirectUrl = $refererUrl;
        } else {
            $redirectUrl = $this->_redirect->getRedirectUrl($this->_url->getUrl('*/*'));
        }
        $resultRedirect->setUrl($redirectUrl);
        return $resultRedirect;
    }
}

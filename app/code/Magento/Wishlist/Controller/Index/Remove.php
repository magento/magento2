<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Wishlist\Controller\Index;

use Exception;
use Magento\Framework\App\Action;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Controller\ResultFactory;
use Magento\Wishlist\Controller\AbstractIndex;
use Magento\Wishlist\Controller\WishlistProviderInterface;
use Magento\Wishlist\Helper\Data;
use Magento\Wishlist\Model\Item;
use Magento\Wishlist\Model\Product\AttributeValueProvider;

/**
 * Wishlist Remove Controller
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Remove extends AbstractIndex implements Action\HttpPostActionInterface
{
    /**
     * @param Action\Context $context
     * @param WishlistProviderInterface $wishlistProvider
     * @param Validator $formKeyValidator
     * @param AttributeValueProvider|null $attributeValueProvider
     */
    public function __construct(
        Action\Context $context,
        protected readonly WishlistProviderInterface $wishlistProvider,
        protected readonly Validator $formKeyValidator,
        private ?AttributeValueProvider $attributeValueProvider = null
    ) {
        $this->attributeValueProvider = $attributeValueProvider
            ?: ObjectManager::getInstance()->get(AttributeValueProvider::class);
        parent::__construct($context);
    }

    /**
     * Remove item
     *
     * @return Redirect
     * @throws NotFoundException
     */
    public function execute()
    {
        /** @var Redirect $resultRedirect */
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
            $productName = $this->attributeValueProvider
                ->getRawAttributeValue($item->getProductId(), 'name');
            $this->messageManager->addComplexSuccessMessage(
                'removeWishlistItemSuccessMessage',
                [
                    'product_name' => $productName,
                ]
            );
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage(
                __('We can\'t delete the item from Wish List right now because of an error: %1.', $e->getMessage())
            );
        } catch (Exception $e) {
            $this->messageManager->addErrorMessage(__('We can\'t delete the item from the Wish List right now.'));
        }

        $this->_objectManager->get(Data::class)->calculate();
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

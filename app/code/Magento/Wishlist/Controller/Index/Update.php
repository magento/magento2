<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Wishlist\Controller\Index;

use Exception;
use Magento\Framework\App\Action;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Controller\ResultFactory;
use Magento\Wishlist\Controller\AbstractIndex;
use Magento\Wishlist\Controller\WishlistProviderInterface;
use Magento\Wishlist\Helper\Data;
use Magento\Wishlist\Model\Item;
use Magento\Wishlist\Model\LocaleQuantityProcessor;
use Psr\Log\LoggerInterface;

/**
 * Controller for updating wishlists
 */
class Update extends AbstractIndex implements HttpPostActionInterface
{
    /**
     * @var Validator
     */
    protected $_formKeyValidator;

    /**
     * @param Action\Context $context
     * @param Validator $formKeyValidator
     * @param WishlistProviderInterface $wishlistProvider
     * @param LocaleQuantityProcessor $quantityProcessor
     */
    public function __construct(
        Action\Context $context,
        Validator $formKeyValidator,
        protected readonly WishlistProviderInterface $wishlistProvider,
        protected readonly LocaleQuantityProcessor $quantityProcessor
    ) {
        $this->_formKeyValidator = $formKeyValidator;
        parent::__construct($context);
    }

    /**
     * Update wishlist item comments
     *
     * @return Redirect
     * @throws NotFoundException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        if (!$this->_formKeyValidator->validate($this->getRequest())) {
            $resultRedirect->setPath('*/*/');
            return $resultRedirect;
        }
        $wishlist = $this->wishlistProvider->getWishlist();
        if (!$wishlist) {
            throw new NotFoundException(__('Page not found.'));
        }

        $post = $this->getRequest()->getPostValue();
        $resultRedirect->setPath('*', ['wishlist_id' => $wishlist->getId()]);
        if (!$post) {
            return $resultRedirect;
        }

        if (isset($post['description']) && is_array($post['description'])) {
            $updatedItems = 0;

            foreach ($post['description'] as $itemId => $description) {
                $item = $this->_objectManager->create(Item::class)->load($itemId);
                if ($item->getWishlistId() != $wishlist->getId()) {
                    continue;
                }

                // Extract new values
                $description = (string)$description;

                if ($description == $this->_objectManager->get(
                    Data::class
                )->defaultCommentString()
                ) {
                    $description = '';
                }

                $qty = null;
                if (isset($post['qty'][$itemId])) {
                    $qty = $this->quantityProcessor->process($post['qty'][$itemId]);
                }
                if ($qty === null) {
                    $qty = $item->getQty();
                    if (!$qty) {
                        $qty = 1;
                    }
                } elseif (0 == $qty) {
                    try {
                        $item->delete();
                    } catch (Exception $e) {
                        $this->_objectManager->get(LoggerInterface::class)->critical($e);
                        $this->messageManager->addErrorMessage(__('We can\'t delete item from Wish List right now.'));
                    }
                }

                // Check that we need to save
                if ($item->getDescription() == $description && $item->getQty() == $qty) {
                    continue;
                }
                try {
                    $item->setDescription($description)->setQty($qty)->save();
                    $this->messageManager->addSuccessMessage(
                        __('%1 has been updated in your Wish List.', $item->getProduct()->getName())
                    );
                    $updatedItems++;
                } catch (Exception $e) {
                    $this->messageManager->addErrorMessage(
                        __(
                            'Can\'t save description %1',
                            $this->_objectManager->get(Escaper::class)->escapeHtml($description)
                        )
                    );
                }
            }

            // save wishlist model for setting date of last update
            if ($updatedItems) {
                try {
                    $wishlist->save();
                    $this->_objectManager->get(Data::class)->calculate();
                } catch (Exception $e) {
                    $this->messageManager->addErrorMessage(__('Can\'t update wish list'));
                }
            }
        }

        if (isset($post['save_and_share'])) {
            $resultRedirect->setPath('*/*/share', ['wishlist_id' => $wishlist->getId()]);
        }

        return $resultRedirect;
    }
}

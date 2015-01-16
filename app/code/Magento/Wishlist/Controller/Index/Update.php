<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Wishlist\Controller\Index;

use Magento\Framework\App\Action;
use Magento\Framework\App\Action\NotFoundException;
use Magento\Framework\App\ResponseInterface;
use Magento\Wishlist\Controller\IndexInterface;

class Update extends Action\Action implements IndexInterface
{
    /**
     * @var \Magento\Wishlist\Controller\WishlistProviderInterface
     */
    protected $wishlistProvider;

    /**
     * @var \Magento\Core\App\Action\FormKeyValidator
     */
    protected $_formKeyValidator;

    /**
     * @var \Magento\Wishlist\Model\LocaleQuantityProcessor
     */
    protected $quantityProcessor;

    /**
     * @param Action\Context $context
     * @param \Magento\Core\App\Action\FormKeyValidator $formKeyValidator
     * @param \Magento\Wishlist\Controller\WishlistProviderInterface $wishlistProvider
     * @param \Magento\Wishlist\Model\LocaleQuantityProcessor $quantityProcessor
     */
    public function __construct(
        Action\Context $context,
        \Magento\Core\App\Action\FormKeyValidator $formKeyValidator,
        \Magento\Wishlist\Controller\WishlistProviderInterface $wishlistProvider,
        \Magento\Wishlist\Model\LocaleQuantityProcessor $quantityProcessor
    ) {
        $this->_formKeyValidator = $formKeyValidator;
        $this->wishlistProvider = $wishlistProvider;
        $this->quantityProcessor = $quantityProcessor;
        parent::__construct($context);
    }

    /**
     * Update wishlist item comments
     *
     * @return ResponseInterface|void
     * @throws NotFoundException
     */
    public function execute()
    {
        if (!$this->_formKeyValidator->validate($this->getRequest())) {
            return $this->_redirect('*/*/');
        }
        $wishlist = $this->wishlistProvider->getWishlist();
        if (!$wishlist) {
            throw new NotFoundException();
        }

        $post = $this->getRequest()->getPost();
        if ($post && isset($post['description']) && is_array($post['description'])) {
            $updatedItems = 0;

            foreach ($post['description'] as $itemId => $description) {
                $item = $this->_objectManager->create('Magento\Wishlist\Model\Item')->load($itemId);
                if ($item->getWishlistId() != $wishlist->getId()) {
                    continue;
                }

                // Extract new values
                $description = (string)$description;

                if ($description == $this->_objectManager->get('Magento\Wishlist\Helper\Data')->defaultCommentString()
                ) {
                    $description = '';
                } elseif (!strlen($description)) {
                    $description = $item->getDescription();
                }

                $qty = null;
                if (isset($post['qty'][$itemId])) {
                    $qty = $this->quantityProcessor->process($post['qty'][$itemId]);
                }
                if (is_null($qty)) {
                    $qty = $item->getQty();
                    if (!$qty) {
                        $qty = 1;
                    }
                } elseif (0 == $qty) {
                    try {
                        $item->delete();
                    } catch (\Exception $e) {
                        $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
                        $this->messageManager->addError(__('Can\'t delete item from wishlist'));
                    }
                }

                // Check that we need to save
                if ($item->getDescription() == $description && $item->getQty() == $qty) {
                    continue;
                }
                try {
                    $item->setDescription($description)->setQty($qty)->save();
                    $updatedItems++;
                } catch (\Exception $e) {
                    $this->messageManager->addError(
                        __(
                            'Can\'t save description %1',
                            $this->_objectManager->get('Magento\Framework\Escaper')->escapeHtml($description)
                        )
                    );
                }
            }

            // save wishlist model for setting date of last update
            if ($updatedItems) {
                try {
                    $wishlist->save();
                    $this->_objectManager->get('Magento\Wishlist\Helper\Data')->calculate();
                } catch (\Exception $e) {
                    $this->messageManager->addError(__('Can\'t update wish list'));
                }
            }

            if (isset($post['save_and_share'])) {
                $this->_redirect('*/*/share', ['wishlist_id' => $wishlist->getId()]);
                return;
            }
        }
        $this->_redirect('*', ['wishlist_id' => $wishlist->getId()]);
    }
}

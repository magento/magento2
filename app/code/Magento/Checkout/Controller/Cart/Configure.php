<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Controller\Cart;

use Magento\Framework;

class Configure extends \Magento\Checkout\Controller\Cart
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param \Magento\Checkout\Model\Cart $cart
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        Framework\App\Action\Context $context,
        Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magento\Checkout\Model\Cart $cart,
        Framework\View\Result\PageFactory $resultPageFactory
    ) {
        parent::__construct(
            $context,
            $scopeConfig,
            $checkoutSession,
            $storeManager,
            $formKeyValidator,
            $cart
        );
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Action to reconfigure cart item
     *
     * @return \Magento\Framework\View\Result\Page|\Magento\Framework\Controller\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
     */
    public function execute()
    {
        // Extract item and product to configure
        $id = (int)$this->getRequest()->getParam('id');
        $productId = (int)$this->getRequest()->getParam('product_id');
        $quoteItem = null;
        if ($id) {
            $quoteItem = $this->cart->getQuote()->getItemById($id);
        }

        if (!$quoteItem || $productId != $quoteItem->getProduct()->getId()) {
            $this->messageManager->addError(__("We can't find the quote item."));
            return $this->resultRedirectFactory->create()->setPath('checkout/cart');
        }

        $params = new \Magento\Framework\Object();
        $params->setCategoryId(false);
        $params->setConfigureMode(true);
        $params->setBuyRequest($quoteItem->getBuyRequest());

        $resultPage = $this->resultPageFactory->create();
        $this->_objectManager->get('Magento\Catalog\Helper\Product\View')
            ->prepareAndRender(
                $resultPage,
                $quoteItem->getProduct()->getId(),
                $this,
                $params
            );
        return $resultPage;
    }

    /**
     * {@inheritdoc}
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function getDefaultResult()
    {
        return $this->_goBack();
    }
}

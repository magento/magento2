<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Controller\Sidebar;

use Magento\Checkout\Controller\Cart;
use Magento\Checkout\Model\Cart as CustomerCart;
use Magento\Checkout\Model\Session;
use Magento\Checkout\Model\Sidebar;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Response\Http;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\StoreManagerInterface;

class RemoveItem extends Cart
{
    /**
     * @var Sidebar
     */
    protected $sidebar;

    /**
     * @param Context $context
     * @param ScopeConfigInterface $scopeConfig
     * @param Session $checkoutSession
     * @param StoreManagerInterface $storeManager
     * @param Validator $formKeyValidator
     * @param CustomerCart $cart
     * @param RedirectFactory $resultRedirectFactory
     * @param Sidebar $sidebar
     */
    public function __construct(
        Context $context,
        ScopeConfigInterface $scopeConfig,
        Session $checkoutSession,
        StoreManagerInterface $storeManager,
        Validator $formKeyValidator,
        CustomerCart $cart,
        RedirectFactory $resultRedirectFactory,
        Sidebar $sidebar
    ) {
        $this->sidebar = $sidebar;
        parent::__construct(
            $context,
            $scopeConfig,
            $checkoutSession,
            $storeManager,
            $formKeyValidator,
            $cart,
            $resultRedirectFactory
        );
    }

    /**
     * @return $this
     */
    public function execute()
    {
        $itemId = (int)$this->getRequest()->getParam('item_id');
        try {
            $this->sidebar->checkQuoteItem($itemId);
            $this->sidebar->removeQuoteItem($itemId);
            return $this->jsonResponse();
        } catch (LocalizedException $e) {
            return $this->jsonResponse($e->getMessage());
        } catch (\Exception $e) {
            $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
            return $this->jsonResponse($e->getMessage());
        }
    }

    /**
     * Compile JSON response
     *
     * @param string $error
     * @return Http
     */
    public function jsonResponse($error = '')
    {
        return $this->getResponse()->representJson(
            $this->_objectManager->get('Magento\Framework\Json\Helper\Data')
                ->jsonEncode($this->sidebar->getResponseData($error))
        );
    }
}

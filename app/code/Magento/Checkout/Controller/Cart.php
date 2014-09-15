<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Checkout\Controller;

use Magento\Checkout\Model\Cart as CustomerCart;
use \Magento\Catalog\Controller\Product\View\ViewInterface;

/**
 * Shopping cart controller
 */
class Cart extends \Magento\Framework\App\Action\Action implements ViewInterface
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Framework\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Core\App\Action\FormKeyValidator
     */
    protected $_formKeyValidator;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $cart;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     * @param \Magento\Core\App\Action\FormKeyValidator $formKeyValidator
     * @param CustomerCart $cart
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\StoreManagerInterface $storeManager,
        \Magento\Core\App\Action\FormKeyValidator $formKeyValidator,
        CustomerCart $cart
    ) {
        $this->_formKeyValidator = $formKeyValidator;
        $this->_scopeConfig = $scopeConfig;
        $this->_checkoutSession = $checkoutSession;
        $this->_storeManager = $storeManager;
        $this->cart = $cart;
        parent::__construct($context);
    }

    /**
     * Set back redirect url to response
     *
     * @return $this
     */
    protected function _goBack()
    {
        $returnUrl = $this->getRequest()->getParam('return_url');
        if ($returnUrl && $this->_isInternalUrl($returnUrl)) {
            $this->messageManager->getMessages()->clear();
            $this->getResponse()->setRedirect($returnUrl);
        } elseif (!$this->_scopeConfig->getValue(
            'checkout/cart/redirect_to_cart',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        ) && !$this->getRequest()->getParam(
            'in_cart'
        ) && ($backUrl = $this->_redirect->getRefererUrl())
        ) {
            $this->getResponse()->setRedirect($backUrl);
        } else {
            if ($this->getRequest()->getActionName() == 'add' && !$this->getRequest()->getParam('in_cart')) {
                $this->_checkoutSession->setContinueShoppingUrl($this->_redirect->getRefererUrl());
            }
            $this->_redirect('checkout/cart');
        }
        return $this;
    }

    /**
     * Check if URL corresponds store
     *
     * @param string $url
     * @return bool
     */
    protected function _isInternalUrl($url)
    {
        if (strpos($url, 'http') === false) {
            return false;
        }

        /**
         * Url must start from base secure or base unsecure url
         */
        /** @var $store \Magento\Store\Model\Store */
        $store = $this->_storeManager->getStore();
        $unsecure = strpos($url, $store->getBaseUrl()) === 0;
        $secure = strpos($url, $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_LINK, true)) === 0;
        return $unsecure || $secure;
    }
}

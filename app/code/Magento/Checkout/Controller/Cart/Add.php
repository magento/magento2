<?php
/**
 *
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
namespace Magento\Checkout\Controller\Cart;

class Add extends \Magento\Checkout\Controller\Cart
{
    /**
     * Initialize product instance from request data
     *
     * @return \Magento\Catalog\Model\Product || false
     */
    protected function _initProduct()
    {
        $productId = (int)$this->getRequest()->getParam('product');
        if ($productId) {
            $storeId = $this->_objectManager->get('Magento\Framework\StoreManagerInterface')->getStore()->getId();
            $product = $this->_objectManager->create(
                'Magento\Catalog\Model\Product'
            )->setStoreId(
                $storeId
            )->load(
                $productId
            );
            if ($product->getId()) {
                return $product;
            }
        }
        return false;
    }

    /**
     * Add product to shopping cart action
     *
     * @return void
     */
    public function execute()
    {
        $params = $this->getRequest()->getParams();
        try {
            if (isset($params['qty'])) {
                $filter = new \Zend_Filter_LocalizedToNormalized(
                    array('locale' => $this->_objectManager->get('Magento\Framework\Locale\ResolverInterface')->getLocaleCode())
                );
                $params['qty'] = $filter->filter($params['qty']);
            }

            $product = $this->_initProduct();
            $related = $this->getRequest()->getParam('related_product');

            /**
             * Check product availability
             */
            if (!$product) {
                $this->_goBack();
                return;
            }

            $this->cart->addProduct($product, $params);
            if (!empty($related)) {
                $this->cart->addProductsByIds(explode(',', $related));
            }

            $this->cart->save();

            $this->_checkoutSession->setCartWasUpdated(true);

            /**
             * @todo remove wishlist observer processAddToCart
             */
            $this->_eventManager->dispatch(
                'checkout_cart_add_product_complete',
                array('product' => $product, 'request' => $this->getRequest(), 'response' => $this->getResponse())
            );

            if (!$this->_checkoutSession->getNoCartRedirect(true)) {
                if (!$this->cart->getQuote()->getHasError()) {
                    $message = __(
                        'You added %1 to your shopping cart.',
                        $this->_objectManager->get('Magento\Framework\Escaper')->escapeHtml($product->getName())
                    );
                    $this->messageManager->addSuccess($message);
                }
                $this->_goBack();
            }
        } catch (\Magento\Framework\Model\Exception $e) {
            if ($this->_checkoutSession->getUseNotice(true)) {
                $this->messageManager->addNotice(
                    $this->_objectManager->get('Magento\Framework\Escaper')->escapeHtml($e->getMessage())
                );
            } else {
                $messages = array_unique(explode("\n", $e->getMessage()));
                foreach ($messages as $message) {
                    $this->messageManager->addError(
                        $this->_objectManager->get('Magento\Framework\Escaper')->escapeHtml($message)
                    );
                }
            }

            $url = $this->_checkoutSession->getRedirectUrl(true);
            if ($url) {
                $this->getResponse()->setRedirect($url);
            } else {
                $cartUrl = $this->_objectManager->get('Magento\Checkout\Helper\Cart')->getCartUrl();
                $this->getResponse()->setRedirect($this->_redirect->getRedirectUrl($cartUrl));
            }
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('We cannot add this item to your shopping cart'));
            $this->_objectManager->get('Magento\Framework\Logger')->logException($e);
            $this->_goBack();
        }
    }
}

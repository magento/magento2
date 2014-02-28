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
 * @category    Magento
 * @package     Magento_Wishlist
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Wishlist shared items controllers
 *
 * @category    Magento
 * @package     Magento_Wishlist
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Wishlist\Controller;

class Shared extends AbstractController
{
    /**
     * Core registry
     *
     * @var \Magento\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\App\Action\Context $context
     * @param \Magento\Core\App\Action\FormKeyValidator $formKeyValidator
     * @param \Magento\Registry $coreRegistry
     */
    public function __construct(
        \Magento\App\Action\Context $context,
        \Magento\Core\App\Action\FormKeyValidator $formKeyValidator,
        \Magento\Registry $coreRegistry
    ) {
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context, $formKeyValidator);
    }

    /**
     * Retrieve wishlist instance by requested code
     *
     * @return \Magento\Wishlist\Model\Wishlist|false
     */
    protected function _getWishlist()
    {
        $code     = (string)$this->getRequest()->getParam('code');
        if (empty($code)) {
            return false;
        }

        $wishlist = $this->_objectManager->create('Magento\Wishlist\Model\Wishlist')->loadByCode($code);
        if (!$wishlist->getId()) {
            return false;
        }

        $this->_objectManager->get('Magento\Checkout\Model\Session')->setSharedWishlist($code);

        return $wishlist;
    }

    /**
     * Shared wishlist view page
     *
     * @return void
     */
    public function indexAction()
    {
        $wishlist   = $this->_getWishlist();
        $customerId = $this->_objectManager->get('Magento\Customer\Model\Session')->getCustomerId();

        if ($wishlist && $wishlist->getCustomerId() && $wishlist->getCustomerId() == $customerId) {
            $this->getResponse()->setRedirect(
                $this->_objectManager->get('Magento\Wishlist\Helper\Data')->getListUrl($wishlist->getId())
            );
            return;
        }

        $this->_coreRegistry->register('shared_wishlist', $wishlist);

        $this->_view->loadLayout();
        $this->_view->getLayout()->initMessages();
        $this->_view->renderLayout();
    }

    /**
     * Add shared wishlist item to shopping cart
     *
     * If Product has required options - redirect
     * to product view page with message about needed defined required options
     *
     * @return \Zend_Controller_Response_Abstract
     */
    public function cartAction()
    {
        $itemId = (int) $this->getRequest()->getParam('item');

        /* @var $item \Magento\Wishlist\Model\Item */
        $item = $this->_objectManager->create('Magento\Wishlist\Model\Item')->load($itemId);


        /* @var $session \Magento\Session\Generic */
        $session    = $this->_objectManager->get('Magento\Wishlist\Model\Session');
        $cart       = $this->_objectManager->get('Magento\Checkout\Model\Cart');

        $redirectUrl = $this->_redirect->getRefererUrl();

        try {
            $options = $this->_objectManager->create('Magento\Wishlist\Model\Item\Option')->getCollection()
                    ->addItemFilter(array($itemId));
            $item->setOptions($options->getOptionsByItem($itemId));

            $item->addToCart($cart);
            $cart->save()->getQuote()->collectTotals();

            if ($this->_objectManager->get('Magento\Checkout\Helper\Cart')->getShouldRedirectToCart()) {
                $redirectUrl = $this->_objectManager->get('Magento\Checkout\Helper\Cart')->getCartUrl();
            }
        } catch (\Magento\Core\Exception $e) {
            if ($e->getCode() == \Magento\Wishlist\Model\Item::EXCEPTION_CODE_NOT_SALABLE) {
                $this->messageManager->addError(__('This product(s) is out of stock.'));
            } else {
                $this->messageManager->addNotice($e->getMessage());
                $redirectUrl = $item->getProductUrl();
            }
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('Cannot add item to shopping cart'));
        }

        return $this->getResponse()->setRedirect($redirectUrl);
    }
}

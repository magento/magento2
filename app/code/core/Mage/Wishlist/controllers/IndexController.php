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
 * @category    Mage
 * @package     Mage_Wishlist
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Wishlist front controller
 *
 * @category    Mage
 * @package     Mage_Wishlist
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Wishlist_IndexController
    extends Mage_Wishlist_Controller_Abstract
    implements Mage_Catalog_Controller_Product_View_Interface
{
    /**
     * Action list where need check enabled cookie
     *
     * @var array
     */
    protected $_cookieCheckActions = array('add');

    /**
     * If true, authentication in this controller (wishlist) could be skipped
     *
     * @var bool
     */
    protected $_skipAuthentication = false;

    public function preDispatch()
    {
        parent::preDispatch();

        if (!$this->_skipAuthentication && !Mage::getSingleton('Mage_Customer_Model_Session')->authenticate($this)) {
            $this->setFlag('', 'no-dispatch', true);
            $customerSession = Mage::getSingleton('Mage_Customer_Model_Session');
            if (!$customerSession->getBeforeWishlistUrl()) {
                $customerSession->setBeforeWishlistUrl($this->_getRefererUrl());
            }
            $customerSession->setBeforeWishlistRequest($this->getRequest()->getParams());
        }
        if (!Mage::getStoreConfigFlag('wishlist/general/active')) {
            $this->norouteAction();
            return;
        }
    }

    /**
     * Set skipping authentication in actions of this controller (wishlist)
     *
     * @return Mage_Wishlist_IndexController
     */
    public function skipAuthentication()
    {
        $this->_skipAuthentication = true;
        return $this;
    }

    /**
     * Retrieve wishlist object
     * @param int $wishlistId
     * @return Mage_Wishlist_Model_Wishlist|bool
     */
    protected function _getWishlist($wishlistId = null)
    {
        $wishlist = Mage::registry('wishlist');
        if ($wishlist) {
            return $wishlist;
        }

        try {
            if (!$wishlistId) {
                $wishlistId = $this->getRequest()->getParam('wishlist_id');
            }
            $customerId = Mage::getSingleton('Mage_Customer_Model_Session')->getCustomerId();
            /* @var Mage_Wishlist_Model_Wishlist $wishlist */
            $wishlist = Mage::getModel('Mage_Wishlist_Model_Wishlist');
            if ($wishlistId) {
                $wishlist->load($wishlistId);
            } else {
                $wishlist->loadByCustomer($customerId, true);
            }

            if (!$wishlist->getId() || $wishlist->getCustomerId() != $customerId) {
                $wishlist = null;
                Mage::throwException(
                    Mage::helper('Mage_Wishlist_Helper_Data')->__("Requested wishlist doesn't exist")
                );
            }

            Mage::register('wishlist', $wishlist);
        } catch (Mage_Core_Exception $e) {
            Mage::getSingleton('Mage_Wishlist_Model_Session')->addError($e->getMessage());
            return false;
        } catch (Exception $e) {
            Mage::getSingleton('Mage_Wishlist_Model_Session')->addException($e,
                Mage::helper('Mage_Wishlist_Helper_Data')->__('Wishlist could not be created.')
            );
            return false;
        }

        return $wishlist;
    }

    /**
     * Display customer wishlist
     */
    public function indexAction()
    {
        if (!$this->_getWishlist()) {
            return $this->norouteAction();
        }
        $this->loadLayout();

        $session = Mage::getSingleton('Mage_Customer_Model_Session');
        $block   = $this->getLayout()->getBlock('customer.wishlist');
        $referer = $session->getAddActionReferer(true);
        if ($block) {
            $block->setRefererUrl($this->_getRefererUrl());
            if ($referer) {
                $block->setRefererUrl($referer);
            }
        }

        $this->_initLayoutMessages('Mage_Customer_Model_Session');
        $this->_initLayoutMessages('Mage_Checkout_Model_Session');
        $this->_initLayoutMessages('Mage_Catalog_Model_Session');
        $this->_initLayoutMessages('Mage_Wishlist_Model_Session');

        $this->renderLayout();
    }

    /**
     * Adding new item
     */
    public function addAction()
    {
        $wishlist = $this->_getWishlist();
        if (!$wishlist) {
            return $this->norouteAction();
        }

        $session = Mage::getSingleton('Mage_Customer_Model_Session');

        $productId = (int) $this->getRequest()->getParam('product');
        if (!$productId) {
            $this->_redirect('*/');
            return;
        }

        $product = Mage::getModel('Mage_Catalog_Model_Product')->load($productId);
        if (!$product->getId() || !$product->isVisibleInCatalog()) {
            $session->addError($this->__('Cannot specify product.'));
            $this->_redirect('*/');
            return;
        }

        try {
            $requestParams = $this->getRequest()->getParams();
            if ($session->getBeforeWishlistRequest()) {
                $requestParams = $session->getBeforeWishlistRequest();
                $session->unsBeforeWishlistRequest();
            }
            $buyRequest = new Varien_Object($requestParams);

            $result = $wishlist->addNewItem($product, $buyRequest);
            if (is_string($result)) {
                Mage::throwException($result);
            }
            $wishlist->save();

            Mage::dispatchEvent(
                'wishlist_add_product',
                array(
                    'wishlist'  => $wishlist,
                    'product'   => $product,
                    'item'      => $result
                )
            );

            $referer = $session->getBeforeWishlistUrl();
            if ($referer) {
                $session->setBeforeWishlistUrl(null);
            } else {
                $referer = $this->_getRefererUrl();
            }

            /**
             *  Set referer to avoid referring to the compare popup window
             */
            $session->setAddActionReferer($referer);

            /** @var $helper Mage_Wishlist_Helper_Data */
            $helper = Mage::helper('Mage_Wishlist_Helper_Data')->calculate();
            $message = $this->__('%1$s has been added to your wishlist. Click <a href="%2$s">here</a> to continue shopping.', $helper->escapeHtml($product->getName()), Mage::helper('Mage_Core_Helper_Data')->escapeUrl($referer));
            $session->addSuccess($message);
        }
        catch (Mage_Core_Exception $e) {
            $session->addError($this->__('An error occurred while adding item to wishlist: %s', $e->getMessage()));
        }
        catch (Exception $e) {
            $session->addError($this->__('An error occurred while adding item to wishlist.'));
            Mage::logException($e);
        }

        $this->_redirect('*', array('wishlist_id' => $wishlist->getId()));
    }

    /**
     * Action to reconfigure wishlist item
     */
    public function configureAction()
    {
        $id = (int) $this->getRequest()->getParam('id');
        try {
            /* @var $item Mage_Wishlist_Model_Item */
            $item = Mage::getModel('Mage_Wishlist_Model_Item');
            $item->loadWithOptions($id);
            if (!$item->getId()) {
                Mage::throwException($this->__('Cannot load wishlist item'));
            }
            $wishlist = $this->_getWishlist($item->getWishlistId());
            if (!$wishlist) {
                return $this->norouteAction();
            }

            Mage::register('wishlist_item', $item);

            $params = new Varien_Object();
            $params->setCategoryId(false);
            $params->setConfigureMode(true);
            $buyRequest = $item->getBuyRequest();
            if (!$buyRequest->getQty() && $item->getQty()) {
                $buyRequest->setQty($item->getQty());
            }
            if ($buyRequest->getQty() && !$item->getQty()) {
                $item->setQty($buyRequest->getQty());
                Mage::helper('Mage_Wishlist_Helper_Data')->calculate();
            }
            $params->setBuyRequest($buyRequest);
            Mage::helper('Mage_Catalog_Helper_Product_View')->prepareAndRender($item->getProductId(), $this, $params);
        } catch (Mage_Core_Exception $e) {
            Mage::getSingleton('Mage_Customer_Model_Session')->addError($e->getMessage());
            $this->_redirect('*');
            return;
        } catch (Exception $e) {
            Mage::getSingleton('Mage_Customer_Model_Session')->addError($this->__('Cannot configure product'));
            Mage::logException($e);
            $this->_redirect('*');
            return;
        }
    }

    /**
     * Action to accept new configuration for a wishlist item
     */
    public function updateItemOptionsAction()
    {
        $session = Mage::getSingleton('Mage_Customer_Model_Session');
        $productId = (int) $this->getRequest()->getParam('product');
        if (!$productId) {
            $this->_redirect('*/');
            return;
        }

        $product = Mage::getModel('Mage_Catalog_Model_Product')->load($productId);
        if (!$product->getId() || !$product->isVisibleInCatalog()) {
            $session->addError($this->__('Cannot specify product.'));
            $this->_redirect('*/');
            return;
        }

        try {
            $id = (int) $this->getRequest()->getParam('id');
            /* @var Mage_Wishlist_Model_Item */
            $item = Mage::getModel('Mage_Wishlist_Model_Item');
            $item->load($id);
            $wishlist = $this->_getWishlist($item->getWishlistId());
            if (!$wishlist) {
                $this->_redirect('*/');
                return;
            }

            $buyRequest = new Varien_Object($this->getRequest()->getParams());

            $wishlist->updateItem($id, $buyRequest)
                ->save();

            Mage::helper('Mage_Wishlist_Helper_Data')->calculate();
            Mage::dispatchEvent('wishlist_update_item', array(
                'wishlist' => $wishlist, 'product' => $product, 'item' => $wishlist->getItem($id))
            );

            Mage::helper('Mage_Wishlist_Helper_Data')->calculate();

            $message = $this->__('%1$s has been updated in your wishlist.', $product->getName());
            $session->addSuccess($message);
        } catch (Mage_Core_Exception $e) {
            $session->addError($e->getMessage());
        } catch (Exception $e) {
            $session->addError($this->__('An error occurred while updating wishlist.'));
            Mage::logException($e);
        }
        $this->_redirect('*/*', array('wishlist_id' => $wishlist->getId()));
    }

    /**
     * Update wishlist item comments
     */
    public function updateAction()
    {
        if (!$this->_validateFormKey()) {
            return $this->_redirect('*/*/');
        }
        $wishlist = $this->_getWishlist();
        if (!$wishlist) {
            return $this->norouteAction();
        }

        $post = $this->getRequest()->getPost();
        if($post && isset($post['description']) && is_array($post['description'])) {
            $updatedItems = 0;

            foreach ($post['description'] as $itemId => $description) {
                $item = Mage::getModel('Mage_Wishlist_Model_Item')->load($itemId);
                if ($item->getWishlistId() != $wishlist->getId()) {
                    continue;
                }

                // Extract new values
                $description = (string) $description;

                if ($description == Mage::helper('Mage_Wishlist_Helper_Data')->defaultCommentString()) {
                    $description = '';
                } elseif (!strlen($description)) {
                    $description = $item->getDescription();
                }

                $qty = null;
                if (isset($post['qty'][$itemId])) {
                    $qty = $this->_processLocalizedQty($post['qty'][$itemId]);
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
                        Mage::logException($e);
                        Mage::getSingleton('Mage_Customer_Model_Session')->addError(
                            $this->__('Can\'t delete item from wishlist')
                        );
                    }
                }

                // Check that we need to save
                if (($item->getDescription() == $description) && ($item->getQty() == $qty)) {
                    continue;
                }
                try {
                    $item->setDescription($description)
                        ->setQty($qty)
                        ->save();
                    $updatedItems++;
                } catch (Exception $e) {
                    Mage::getSingleton('Mage_Customer_Model_Session')->addError(
                        $this->__('Can\'t save description %s', Mage::helper('Mage_Core_Helper_Data')->escapeHtml($description))
                    );
                }
            }

            // save wishlist model for setting date of last update
            if ($updatedItems) {
                try {
                    $wishlist->save();
                    Mage::helper('Mage_Wishlist_Helper_Data')->calculate();
                }
                catch (Exception $e) {
                    Mage::getSingleton('Mage_Customer_Model_Session')->addError($this->__('Can\'t update wishlist'));
                }
            }

            if (isset($post['save_and_share'])) {
                $this->_redirect('*/*/share', array('wishlist_id' => $wishlist->getId()));
                return;
            }
        }
        $this->_redirect('*', array('wishlist_id' => $wishlist->getId()));
    }

    /**
     * Remove item
     */
    public function removeAction()
    {
        $id = (int) $this->getRequest()->getParam('item');
        $item = Mage::getModel('Mage_Wishlist_Model_Item')->load($id);
        if (!$item->getId()) {
            return $this->norouteAction();
        }
        $wishlist = $this->_getWishlist($item->getWishlistId());
        if (!$wishlist) {
            return $this->norouteAction();
        }
        try {
            $item->delete();
            $wishlist->save();
        } catch (Mage_Core_Exception $e) {
            Mage::getSingleton('Mage_Customer_Model_Session')->addError(
                $this->__('An error occurred while deleting the item from wishlist: %s', $e->getMessage())
            );
        } catch(Exception $e) {
            Mage::getSingleton('Mage_Customer_Model_Session')->addError(
                $this->__('An error occurred while deleting the item from wishlist.')
            );
        }

        Mage::helper('Mage_Wishlist_Helper_Data')->calculate();

        $this->_redirectReferer(Mage::getUrl('*/*'));
    }

    /**
     * Add wishlist item to shopping cart and remove from wishlist
     *
     * If Product has required options - item removed from wishlist and redirect
     * to product view page with message about needed defined required options
     */
    public function cartAction()
    {
        $itemId = (int) $this->getRequest()->getParam('item');

        /* @var $item Mage_Wishlist_Model_Item */
        $item = Mage::getModel('Mage_Wishlist_Model_Item')->load($itemId);
        if (!$item->getId()) {
            return $this->_redirect('*/*');
        }
        $wishlist = $this->_getWishlist($item->getWishlistId());
        if (!$wishlist) {
            return $this->_redirect('*/*');
        }

        // Set qty
        $qty = $this->getRequest()->getParam('qty');
        if (is_array($qty)) {
            if (isset($qty[$itemId])) {
                $qty = $qty[$itemId];
            } else {
                $qty = 1;
            }
        }
        $qty = $this->_processLocalizedQty($qty);
        if ($qty) {
            $item->setQty($qty);
        }

        /* @var $session Mage_Wishlist_Model_Session */
        $session    = Mage::getSingleton('Mage_Wishlist_Model_Session');
        $cart       = Mage::getSingleton('Mage_Checkout_Model_Cart');

        $redirectUrl = Mage::getUrl('*/*');

        try {
            $options = Mage::getModel('Mage_Wishlist_Model_Item_Option')->getCollection()
                    ->addItemFilter(array($itemId));
            $item->setOptions($options->getOptionsByItem($itemId));

            $buyRequest = Mage::helper('Mage_Catalog_Helper_Product')->addParamsToBuyRequest(
                $this->getRequest()->getParams(),
                array('current_config' => $item->getBuyRequest())
            );

            $item->mergeBuyRequest($buyRequest);
            $item->addToCart($cart, true);
            $cart->save()->getQuote()->collectTotals();
            $wishlist->save();

            Mage::helper('Mage_Wishlist_Helper_Data')->calculate();

            if (Mage::helper('Mage_Checkout_Helper_Cart')->getShouldRedirectToCart()) {
                $redirectUrl = Mage::helper('Mage_Checkout_Helper_Cart')->getCartUrl();
            } else if ($this->_getRefererUrl()) {
                $redirectUrl = $this->_getRefererUrl();
            }
            Mage::helper('Mage_Wishlist_Helper_Data')->calculate();
        } catch (Mage_Core_Exception $e) {
            if ($e->getCode() == Mage_Wishlist_Model_Item::EXCEPTION_CODE_NOT_SALABLE) {
                $session->addError(Mage::helper('Mage_Wishlist_Helper_Data')->__('This product(s) is currently out of stock'));
            } else if ($e->getCode() == Mage_Wishlist_Model_Item::EXCEPTION_CODE_HAS_REQUIRED_OPTIONS) {
                Mage::getSingleton('Mage_Catalog_Model_Session')->addNotice($e->getMessage());
                $redirectUrl = Mage::getUrl('*/*/configure/', array('id' => $item->getId()));
            } else {
                Mage::getSingleton('Mage_Catalog_Model_Session')->addNotice($e->getMessage());
                $redirectUrl = Mage::getUrl('*/*/configure/', array('id' => $item->getId()));
            }
        } catch (Exception $e) {
            $session->addException($e, Mage::helper('Mage_Wishlist_Helper_Data')->__('Cannot add item to shopping cart'));
        }

        Mage::helper('Mage_Wishlist_Helper_Data')->calculate();

        return $this->_redirectUrl($redirectUrl);
    }

    /**
     * Add cart item to wishlist and remove from cart
     */
    public function fromcartAction()
    {
        $wishlist = $this->_getWishlist();
        if (!$wishlist) {
            return $this->norouteAction();
        }
        $itemId = (int) $this->getRequest()->getParam('item');

        /* @var Mage_Checkout_Model_Cart $cart */
        $cart = Mage::getSingleton('Mage_Checkout_Model_Cart');
        $session = Mage::getSingleton('Mage_Checkout_Model_Session');

        try{
            $item = $cart->getQuote()->getItemById($itemId);
            if (!$item) {
                Mage::throwException(
                    Mage::helper('Mage_Wishlist_Helper_Data')->__("Requested cart item doesn't exist")
                );
            }

            $productId  = $item->getProductId();
            $buyRequest = $item->getBuyRequest();

            $wishlist->addNewItem($productId, $buyRequest);

            $productIds[] = $productId;
            $cart->getQuote()->removeItem($itemId);
            $cart->save();
            Mage::helper('Mage_Wishlist_Helper_Data')->calculate();
            $productName = Mage::helper('Mage_Core_Helper_Data')->escapeHtml($item->getProduct()->getName());
            $wishlistName = Mage::helper('Mage_Core_Helper_Data')->escapeHtml($wishlist->getName());
            $session->addSuccess(
                Mage::helper('Mage_Wishlist_Helper_Data')->__("%s has been moved to wishlist %s", $productName, $wishlistName)
            );
            $wishlist->save();
        } catch (Mage_Core_Exception $e) {
            $session->addError($e->getMessage());
        } catch (Exception $e) {
            $session->addException($e, Mage::helper('Mage_Wishlist_Helper_Data')->__('Cannot move item to wishlist'));
        }

        return $this->_redirectUrl(Mage::helper('Mage_Checkout_Helper_Cart')->getCartUrl());
    }

    /**
     * Prepare wishlist for share
     */
    public function shareAction()
    {
        $this->_getWishlist();
        $this->loadLayout();
        $this->_initLayoutMessages('Mage_Customer_Model_Session');
        $this->_initLayoutMessages('Mage_Wishlist_Model_Session');
        $this->renderLayout();
    }

    /**
     * Share wishlist
     *
     * @return Mage_Core_Controller_Varien_Action|void
     */
    public function sendAction()
    {
        if (!$this->_validateFormKey()) {
            return $this->_redirect('*/*/');
        }

        $wishlist = $this->_getWishlist();
        if (!$wishlist) {
            return $this->norouteAction();
        }

        $emails  = explode(',', $this->getRequest()->getPost('emails'));
        $message = nl2br(htmlspecialchars((string) $this->getRequest()->getPost('message')));
        $error   = false;
        if (empty($emails)) {
            $error = $this->__('Email address can\'t be empty.');
        }
        else {
            foreach ($emails as $index => $email) {
                $email = trim($email);
                if (!Zend_Validate::is($email, 'EmailAddress')) {
                    $error = $this->__('Please input a valid email address.');
                    break;
                }
                $emails[$index] = $email;
            }
        }
        if ($error) {
            Mage::getSingleton('Mage_Wishlist_Model_Session')->addError($error);
            Mage::getSingleton('Mage_Wishlist_Model_Session')->setSharingForm($this->getRequest()->getPost());
            $this->_redirect('*/*/share');
            return;
        }

        $translate = Mage::getSingleton('Mage_Core_Model_Translate');
        /* @var $translate Mage_Core_Model_Translate */
        $translate->setTranslateInline(false);

        try {
            $customer = Mage::getSingleton('Mage_Customer_Model_Session')->getCustomer();

            /*if share rss added rss feed to email template*/
            if ($this->getRequest()->getParam('rss_url')) {
                $rss_url = $this->getLayout()
                    ->createBlock('Mage_Wishlist_Block_Share_Email_Rss')
                    ->setWishlistId($wishlist->getId())
                    ->toHtml();
                $message .=$rss_url;
            }
            $wishlistBlock = $this->getLayout()->createBlock('Mage_Wishlist_Block_Share_Email_Items')->toHtml();

            $emails = array_unique($emails);
            /* @var $emailModel Mage_Core_Model_Email_Template */
            $emailModel = Mage::getModel('Mage_Core_Model_Email_Template');

            $sharingCode = $wishlist->getSharingCode();
            foreach($emails as $email) {
                $emailModel->sendTransactional(
                    Mage::getStoreConfig('wishlist/email/email_template'),
                    Mage::getStoreConfig('wishlist/email/email_identity'),
                    $email,
                    null,
                    array(
                        'customer'      => $customer,
                        'salable'       => $wishlist->isSalable() ? 'yes' : '',
                        'items'         => $wishlistBlock,
                        'addAllLink'    => Mage::getUrl('*/shared/allcart', array('code' => $sharingCode)),
                        'viewOnSiteLink'=> Mage::getUrl('*/shared/index', array('code' => $sharingCode)),
                        'message'       => $message
                    )
                );
            }

            $wishlist->setShared(1);
            $wishlist->save();

            $translate->setTranslateInline(true);

            Mage::dispatchEvent('wishlist_share', array('wishlist'=>$wishlist));
            Mage::getSingleton('Mage_Customer_Model_Session')->addSuccess(
                $this->__('Your Wishlist has been shared.')
            );
            $this->_redirect('*/*', array('wishlist_id' => $wishlist->getId()));
        }
        catch (Exception $e) {
            $translate->setTranslateInline(true);

            Mage::getSingleton('Mage_Wishlist_Model_Session')->addError($e->getMessage());
            Mage::getSingleton('Mage_Wishlist_Model_Session')->setSharingForm($this->getRequest()->getPost());
            $this->_redirect('*/*/share');
        }
    }

    /**
     * Custom options download action
     * @return void
     */
    public function downloadCustomOptionAction()
    {
        $option = Mage::getModel('Mage_Wishlist_Model_Item_Option')->load($this->getRequest()->getParam('id'));

        if (!$option->getId()) {
            return $this->_forward('noRoute');
        }

        $optionId = null;
        if (strpos($option->getCode(), Mage_Catalog_Model_Product_Type_Abstract::OPTION_PREFIX) === 0) {
            $optionId = str_replace(Mage_Catalog_Model_Product_Type_Abstract::OPTION_PREFIX, '', $option->getCode());
            if ((int)$optionId != $optionId) {
                return $this->_forward('noRoute');
            }
        }
        $productOption = Mage::getModel('Mage_Catalog_Model_Product_Option')->load($optionId);

        if (!$productOption
            || !$productOption->getId()
            || $productOption->getProductId() != $option->getProductId()
            || $productOption->getType() != 'file'
        ) {
            return $this->_forward('noRoute');
        }

        try {
            $info      = unserialize($option->getValue());
            $filePath  = Mage::getBaseDir() . $info['quote_path'];
            $secretKey = $this->getRequest()->getParam('key');

            if ($secretKey == $info['secret_key']) {
                $this->_prepareDownloadResponse($info['title'], array(
                    'value' => $filePath,
                    'type'  => 'filename'
                ));
            }

        } catch(Exception $e) {
            $this->_forward('noRoute');
        }
        exit(0);
    }
}

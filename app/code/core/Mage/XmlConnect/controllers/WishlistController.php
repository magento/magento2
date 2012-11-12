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
 * @package     Mage_XmlConnect
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * XmlConnect wishlist controller
 *
 * @category    Mage
 * @package     Mage_XmlConnect
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_XmlConnect_WishlistController extends Mage_XmlConnect_Controller_Action
{
    /**
     * Check if customer is logged in
     *
     * @return null
     */
    public function preDispatch()
    {
        parent::preDispatch();
        if (!$this->_getCustomerSession()->isLoggedIn()) {
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
            $this->_message(
                $this->__('Customer not logged in.'), self::MESSAGE_STATUS_ERROR, array('logged_in' => '0')
            );
            return ;
        }
    }

    /**
     * Get customer session model
     *
     * @return Mage_Customer_Model_Session
     */
    protected function _getCustomerSession()
    {
        return Mage::getSingleton('Mage_Customer_Model_Session');
    }

    /**
     * Retrieve wishlist object
     *
     * @return Mage_Wishlist_Model_Wishlist|false
     */
    protected function _getWishlist()
    {
        try {
            $wishlist = Mage::getModel('Mage_Wishlist_Model_Wishlist')
                ->loadByCustomer($this->_getCustomerSession()->getCustomer(), true);
            Mage::register('wishlist', $wishlist);
        } catch (Mage_Core_Exception $e) {
            $this->_message($e->getMessage(), self::MESSAGE_STATUS_ERROR);
            return false;
        } catch (Exception $e) {
            $this->_message($this->__('Can\'t create wishlist.'), self::MESSAGE_STATUS_ERROR);
            return false;
        }
        return $wishlist;
    }

    /**
     * Display customer wishlist
     *
     * @return null
     */
    public function indexAction()
    {
        $this->_getWishlist();
        try {
            $this->loadLayout(false);
            $this->renderLayout();
        } catch (Mage_Core_Exception $e) {
            $this->_message($e->getMessage(), self::MESSAGE_STATUS_ERROR);
        } catch (Exception $e) {
            Mage::logException($e);
            $this->_message(
                $this->__('An error occurred while loading wishlist.'),
                self::MESSAGE_STATUS_ERROR
            );
        }
    }

    /**
     * Adding new item
     *
     * @return null
     */
    public function addAction()
    {
        $wishlist = $this->_getWishlist();
        if (!$wishlist) {
            return;
        }

        $request = $this->getRequest();
        $productId = (int)$request->getParam('product');
        if (!$productId) {
            $this->_message($this->__('Product was not specified.'), self::MESSAGE_STATUS_ERROR);
            return;
        }

        $product = Mage::getModel('Mage_Catalog_Model_Product')->load($productId);
        if (!$product->getId() || !$product->isVisibleInCatalog()) {
            $this->_message($this->__('Can\'t specify product.'), self::MESSAGE_STATUS_ERROR);
            return;
        }

        try {
            $buyRequest = new Varien_Object($this->getRequest()->getParams());
            $result = $wishlist->addNewItem($product, $buyRequest);
            if (strlen(trim((string)$request->getParam('description')))) {
                $result->setDescription($request->getParam('description'))->save();
            }
            $wishlist->save();

            Mage::dispatchEvent('wishlist_add_product', array(
                'wishlist'  => $wishlist,
                'product'   => $product,
                'item'      => $result
            ));

            Mage::helper('Mage_Wishlist_Helper_Data')->calculate();

            $this->_message(
                $this->__('%1$s has been added to your wishlist.', $product->getName()),
                self::MESSAGE_STATUS_SUCCESS
            );
        } catch (Mage_Core_Exception $e) {
            $this->_message($e->getMessage(), self::MESSAGE_STATUS_ERROR);
        } catch (Exception $e) {
            Mage::logException($e);
            $this->_message(
                $this->__('An error occurred while adding item to wishlist.'), self::MESSAGE_STATUS_ERROR
            );
        }
    }

    /**
     * Remove item
     *
     * @return null
     */
    public function removeAction()
    {
        $wishlist = $this->_getWishlist();
        $id = (int) $this->getRequest()->getParam('item');
        $item = Mage::getModel('Mage_Wishlist_Model_Item')->load($id);

        if ($item->getWishlistId() == $wishlist->getId()) {
            try {
                $item->delete();
                $wishlist->save();
                $this->_message($this->__('Item has been removed from wishlist.'), self::MESSAGE_STATUS_SUCCESS);
            } catch (Mage_Core_Exception $e) {
                $this->_message($e->getMessage(), self::MESSAGE_STATUS_ERROR);
            } catch(Exception $e) {
                $this->_message($this->__('An error occurred while removing item from wishlist.'), self::MESSAGE_STATUS_ERROR);
            }
        } else {
            $this->_message($this->__('Specified item does not exist in wishlist.'), self::MESSAGE_STATUS_ERROR);
        }

        Mage::helper('Mage_Wishlist_Helper_Data')->calculate();
    }

    /**
     * Clear wishlist action
     *
     * @return null
     */
    public function clearAction()
    {
        $wishlist = $this->_getWishlist();
        $items = $wishlist->getItemCollection();

        try {
            foreach ($items as $item) {
                $item->delete();
            }
            $wishlist->save();
            $this->_message($this->__('Wishlist has been cleared.'), self::MESSAGE_STATUS_SUCCESS);
        } catch (Mage_Core_Exception $e) {
            $this->_message($e->getMessage(), self::MESSAGE_STATUS_ERROR);
        } catch(Exception $e) {
            $this->_message(
                $this->__('An error occurred while removing items from wishlist.'), self::MESSAGE_STATUS_ERROR
            );
        }

        Mage::helper('Mage_Wishlist_Helper_Data')->calculate();
    }

    /**
     * Update wishlist item comments
     *
     * @return null
     */
    public function updateAction()
    {
        $post = $this->getRequest()->getPost();
        if ($post && isset($post['description']) && is_array($post['description'])) {
            $wishlist = $this->_getWishlist();
            if (!$wishlist) {
                return;
            }
            $updatedItems = 0;
            $problemsFlag = false;

            foreach ($post['description'] as $itemId => $description) {
                /** @var $item Mage_Wishlist_Model_Item */
                $item = Mage::getModel('Mage_Wishlist_Model_Item')->load($itemId);
                $description = (string) $description;
                if ($item->getWishlistId() != $wishlist->getId()) {
                    continue;
                }
                try {
                    $item->setDescription($description)->save();
                    $updatedItems++;
                } catch (Exception $e) {
                    $problemsFlag = true;
                }
            }

            // save wishlist model for setting date of last update
            if ($updatedItems) {
                try {
                    $wishlist->save();
                    if ($problemsFlag) {
                        $message = $this->__('Wishlist has been updated. But there are accrued some errors while updating some items.');
                    } else {
                        $message = $this->__('Wishlist has been updated.');
                    }
                    $this->_message($message, self::MESSAGE_STATUS_SUCCESS);
                }
                catch (Exception $e) {
                    $this->_message(
                        $this->__('Items were updated. But can\'t update wishlist.'),
                        self::MESSAGE_STATUS_SUCCESS
                    );
                }
            } else {
                $this->_message($this->__('No items were updated.'), self::MESSAGE_STATUS_ERROR);
            }
        } else {
            $this->_message($this->__('No items were specifed to update.'), self::MESSAGE_STATUS_ERROR);
        }
    }

    /**
     * Add wishlist item to shopping cart and remove from wishlist
     *
     * If Product has required options - item removed from wishlist and redirect
     * to product view page with message about needed defined required options
     *
     * @return null
     */
    public function cartAction()
    {
        $wishlist   = $this->_getWishlist();
        if (!$wishlist) {
            return;
        }
        $itemId     = (int)$this->getRequest()->getParam('item');

        /* @var $item Mage_Wishlist_Model_Item */
        $item       = Mage::getModel('Mage_Wishlist_Model_Item')->load($itemId);

        if (!$item->getId() || $item->getWishlistId() != $wishlist->getId()) {
            $this->_message($this->__('Invalid item or wishlist.'), self::MESSAGE_STATUS_ERROR);
            return;
        }

        try {
            $cart = Mage::getSingleton('Mage_Checkout_Model_Cart');
            $item->addToCart($cart, true);
            $cart->save()->getQuote()->collectTotals();
            $wishlist->save();
            Mage::helper('Mage_Wishlist_Helper_Data')->calculate();
            $this->_message($this->__('Item has been added to cart.'), self::MESSAGE_STATUS_SUCCESS);

        } catch (Mage_Core_Exception $e) {
            if ($e->getCode() == Mage_Wishlist_Model_Item::EXCEPTION_CODE_NOT_SALABLE) {
                $this->_message($this->__('Product(s) currently out of stock.'), self::MESSAGE_STATUS_ERROR);
            } else if ($e->getCode() == Mage_Wishlist_Model_Item::EXCEPTION_CODE_HAS_REQUIRED_OPTIONS) {
                $item->delete();

                $message = Mage::getModel('Mage_XmlConnect_Model_Simplexml_Element',
                    array('data' => '<message></message>'));
                $message->addChild('status', self::MESSAGE_STATUS_SUCCESS);
                $message->addChild('has_required_options', 1);
                $message->addChild('product_id', $item->getProductId());
                $this->getResponse()->setBody($message->asNiceXml());
            } else {
                $this->_message($e->getMessage(), self::MESSAGE_STATUS_ERROR);
            }
        } catch (Exception $e) {
            $this->_message($this->__('Can\'t add item to shopping cart.'), self::MESSAGE_STATUS_ERROR);
        }

        Mage::helper('Mage_Wishlist_Helper_Data')->calculate();
    }
}

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
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * XmlConnect review controller
 *
 * @category    Mage
 * @package     Mage_Xmlconnect
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_XmlConnect_ReviewController extends Mage_XmlConnect_Controller_Action
{
    /**
     * Initialize and check product
     *
     * @return Mage_Catalog_Model_Product
     */
    protected function _initProduct()
    {
        Mage::dispatchEvent('review_controller_product_init_before', array('controller_action' => $this));

        $productId  = (int) $this->getRequest()->getParam('id');
        $product = $this->_loadProduct($productId);

        try {
            Mage::dispatchEvent('review_controller_product_init', array('product' => $product));
            Mage::dispatchEvent('review_controller_product_init_after', array(
                'product'           => $product,
                'controller_action' => $this
            ));
        } catch (Mage_Core_Exception $e) {
            Mage::logException($e);
            return false;
        }

        return $product;
    }

    /**
     * Load product model with data by passed id.
     * Return false if product was not loaded or has incorrect status.
     *
     * @param int $productId
     * @return bool | Mage_Catalog_Model_Product
     */
    protected function _loadProduct($productId)
    {
        if (!$productId) {
            return false;
        }

        $product = Mage::getModel('Mage_Catalog_Model_Product')->setStoreId(Mage::app()->getStore()->getId())->load($productId);
        /** @var $product Mage_Catalog_Model_Product */
        if (!$product->getId() || !$product->isVisibleInCatalog() || !$product->isVisibleInSiteVisibility()) {
            return false;
        }

        Mage::register('current_product', $product);
        Mage::register('product', $product);

        return $product;
    }

    /**
     * Check if guest is allowed to write review
     *
     * Do check the customer is logged in or guest is allowed to write review
     *
     * @return bool
     */
    protected function _checkGuestAllowed()
    {
        if (Mage::getSingleton('Mage_Customer_Model_Session')->isLoggedIn()
            || Mage::helper('Mage_Review_Helper_Data')->getIsGuestAllowToWrite()
        ) {
            return true;
        }

        $this->_message(
            $this->__('Only registered users can write reviews. Please, log in or register.'),
            self::MESSAGE_STATUS_ERROR
        );
        return false;
    }

    /**
     * Get review form
     *
     * @return null
     */
    public function formAction()
    {
        if (!$this->_checkGuestAllowed()) {
            return;
        }

        try {
            $this->loadLayout(false);
            $this->renderLayout();
        } catch (Mage_Core_Exception $e) {
            $this->_message($e->getMessage(), self::MESSAGE_STATUS_ERROR);
        } catch (Exception $e) {
            $this->_message($this->__('Unable to load review form.'), self::MESSAGE_STATUS_ERROR);
            Mage::logException($e);
        }
    }

    /**
     * Save product review
     *
     * @return null
     */
    public function saveAction()
    {
        if (!$this->_checkGuestAllowed()) {
            return;
        }

        $data   = $this->getRequest()->getPost();
        $rating = $this->getRequest()->getPost('ratings', array());

        $product = $this->_initProduct();
        if ($product && !empty($data)) {
            /** @var $review Mage_Review_Model_Review */
            $review     = Mage::getModel('Mage_Review_Model_Review')->setData($data);
            $validate   = $review->validate();

            if ($validate === true) {
                try {
                    $review->setEntityId($review->getEntityIdByCode(Mage_Review_Model_Review::ENTITY_PRODUCT_CODE))
                        ->setEntityPkValue($product->getId())->setStatusId(Mage_Review_Model_Review::STATUS_PENDING)
                        ->setCustomerId(Mage::getSingleton('Mage_Customer_Model_Session')->getCustomerId())
                        ->setStoreId(Mage::app()->getStore()->getId())
                        ->setStores(array(Mage::app()->getStore()->getId()))->save();

                    foreach ($rating as $ratingId => $optionId) {
                        Mage::getModel('Mage_Rating_Model_Rating')->setRatingId($ratingId)->setReviewId($review->getId())
                            ->setCustomerId(Mage::getSingleton('Mage_Customer_Model_Session')->getCustomerId())
                            ->addOptionVote($optionId, $product->getId());
                    }

                    $review->aggregate();
                    $this->_message(
                        $this->__('Your review has been accepted for moderation.'), self::MESSAGE_STATUS_SUCCESS
                    );
                } catch (Exception $e) {
                    $this->_message($this->__('Unable to post the review.'), self::MESSAGE_STATUS_ERROR);
                    Mage::logException($e);
                }
            } else {
                if (is_array($validate)) {
                    $validate = array_map(array($this, '_trimDot'), $validate);
                    $this->_message(implode('. ', $validate) . '.', self::MESSAGE_STATUS_ERROR);
                } else {
                    $this->_message($this->__('Unable to post the review.'), self::MESSAGE_STATUS_ERROR);
                }
            }
        } else {
            $this->_message($this->__('Unable to post the review.'), self::MESSAGE_STATUS_ERROR);
        }
    }

    /**
     * Trim ending dot (the ".") symbol from string
     *
     * @param string $text
     * @return string
     */
    private function _trimDot($text)
    {
        return trim($text, " \n\r\t.");
    }
}

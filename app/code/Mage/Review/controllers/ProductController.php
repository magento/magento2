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
 * @package     Mage_Review
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Review controller
 *
 * @category   Mage
 * @package    Mage_Review
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Review_ProductController extends Mage_Core_Controller_Front_Action
{

    /**
     * Action list where need check enabled cookie
     *
     * @var array
     */
    protected $_cookieCheckActions = array('post');

    public function preDispatch()
    {
        parent::preDispatch();

        $allowGuest = Mage::helper('Mage_Review_Helper_Data')->getIsGuestAllowToWrite();
        if (!$this->getRequest()->isDispatched()) {
            return;
        }

        $action = $this->getRequest()->getActionName();
        if (!$allowGuest && $action == 'post' && $this->getRequest()->isPost()) {
            if (!Mage::getSingleton('Mage_Customer_Model_Session')->isLoggedIn()) {
                $this->setFlag('', self::FLAG_NO_DISPATCH, true);
                Mage::getSingleton('Mage_Customer_Model_Session')->setBeforeAuthUrl(Mage::getUrl('*/*/*', array('_current' => true)));
                Mage::getSingleton('Mage_Review_Model_Session')->setFormData($this->getRequest()->getPost())
                    ->setRedirectUrl($this->_getRefererUrl());
                $this->_redirectUrl(Mage::helper('Mage_Customer_Helper_Data')->getLoginUrl());
            }
        }

        return $this;
    }
    /**
     * Initialize and check product
     *
     * @return Mage_Catalog_Model_Product
     */
    protected function _initProduct()
    {
        $this->_eventManager->dispatch('review_controller_product_init_before', array('controller_action'=>$this));
        $categoryId = (int) $this->getRequest()->getParam('category', false);
        $productId  = (int) $this->getRequest()->getParam('id');

        $product = $this->_loadProduct($productId);
        if (!$product) {
            return false;
        }

        if ($categoryId) {
            $category = Mage::getModel('Mage_Catalog_Model_Category')->load($categoryId);
            Mage::register('current_category', $category);
        }

        try {
            $this->_eventManager->dispatch('review_controller_product_init', array('product'=>$product));
            $this->_eventManager->dispatch('review_controller_product_init_after', array(
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
     * @return bool|Mage_Catalog_Model_Product
     */
    protected function _loadProduct($productId)
    {
        if (!$productId) {
            return false;
        }

        $product = Mage::getModel('Mage_Catalog_Model_Product')
            ->setStoreId(Mage::app()->getStore()->getId())
            ->load($productId);
        /* @var $product Mage_Catalog_Model_Product */
        if (!$product->getId() || !$product->isVisibleInCatalog() || !$product->isVisibleInSiteVisibility()) {
            return false;
        }

        Mage::register('current_product', $product);
        Mage::register('product', $product);

        return $product;
    }

    /**
     * Load review model with data by passed id.
     * Return false if review was not loaded or review is not approved.
     *
     * @param int $productId
     * @return bool|Mage_Review_Model_Review
     */
    protected function _loadReview($reviewId)
    {
        if (!$reviewId) {
            return false;
        }

        $review = Mage::getModel('Mage_Review_Model_Review')->load($reviewId);
        /* @var $review Mage_Review_Model_Review */
        if (!$review->getId() || !$review->isApproved() || !$review->isAvailableOnStore(Mage::app()->getStore())) {
            return false;
        }

        Mage::register('current_review', $review);

        return $review;
    }

    /**
     * Submit new review action
     *
     */
    public function postAction()
    {
        if ($data = Mage::getSingleton('Mage_Review_Model_Session')->getFormData(true)) {
            $rating = array();
            if (isset($data['ratings']) && is_array($data['ratings'])) {
                $rating = $data['ratings'];
            }
        } else {
            $data   = $this->getRequest()->getPost();
            $rating = $this->getRequest()->getParam('ratings', array());
        }

        if (($product = $this->_initProduct()) && !empty($data)) {
            $session    = Mage::getSingleton('Mage_Core_Model_Session');
            /* @var $session Mage_Core_Model_Session */
            $review     = Mage::getModel('Mage_Review_Model_Review')->setData($data);
            /* @var $review Mage_Review_Model_Review */

            $validate = $review->validate();
            if ($validate === true) {
                try {
                    $review->setEntityId($review->getEntityIdByCode(Mage_Review_Model_Review::ENTITY_PRODUCT_CODE))
                        ->setEntityPkValue($product->getId())
                        ->setStatusId(Mage_Review_Model_Review::STATUS_PENDING)
                        ->setCustomerId(Mage::getSingleton('Mage_Customer_Model_Session')->getCustomerId())
                        ->setStoreId(Mage::app()->getStore()->getId())
                        ->setStores(array(Mage::app()->getStore()->getId()))
                        ->save();

                    foreach ($rating as $ratingId => $optionId) {
                        Mage::getModel('Mage_Rating_Model_Rating')
                        ->setRatingId($ratingId)
                        ->setReviewId($review->getId())
                        ->setCustomerId(Mage::getSingleton('Mage_Customer_Model_Session')->getCustomerId())
                        ->addOptionVote($optionId, $product->getId());
                    }

                    $review->aggregate();
                    $session->addSuccess($this->__('Your review has been accepted for moderation.'));
                }
                catch (Exception $e) {
                    $session->setFormData($data);
                    $session->addError($this->__('We cannot post the review.'));
                }
            }
            else {
                $session->setFormData($data);
                if (is_array($validate)) {
                    foreach ($validate as $errorMessage) {
                        $session->addError($errorMessage);
                    }
                }
                else {
                    $session->addError($this->__('We cannot post the review.'));
                }
            }
        }

        if ($redirectUrl = Mage::getSingleton('Mage_Review_Model_Session')->getRedirectUrl(true)) {
            $this->_redirectUrl($redirectUrl);
            return;
        }
        $this->_redirectReferer();
    }

    /**
     * Show list of product's reviews
     *
     */
    public function listAction()
    {
        if ($product = $this->_initProduct()) {
            Mage::register('productId', $product->getId());

            $design = Mage::getSingleton('Mage_Catalog_Model_Design');
            $settings = $design->getDesignSettings($product);
            if ($settings->getCustomDesign()) {
                $design->applyCustomDesign($settings->getCustomDesign());
            }
            $this->_initProductLayout($product);

            // update breadcrumbs
            if ($breadcrumbsBlock = $this->getLayout()->getBlock('breadcrumbs')) {
                $breadcrumbsBlock->addCrumb('product', array(
                    'label'    => $product->getName(),
                    'link'     => $product->getProductUrl(),
                    'readonly' => true,
                ));
                $breadcrumbsBlock->addCrumb('reviews', array('label' => Mage::helper('Mage_Review_Helper_Data')->__('Product Reviews')));
            }

            $this->renderLayout();
        } elseif (!$this->getResponse()->isRedirect()) {
            $this->_forward('noRoute');
        }
    }

    /**
     * Show details of one review
     *
     */
    public function viewAction()
    {
        $review = $this->_loadReview((int) $this->getRequest()->getParam('id'));
        if (!$review) {
            $this->_forward('noroute');
            return;
        }

        $product = $this->_loadProduct($review->getEntityPkValue());
        if (!$product) {
            $this->_forward('noroute');
            return;
        }

        $this->loadLayout();
        $this->_initLayoutMessages('Mage_Review_Model_Session');
        $this->_initLayoutMessages('Mage_Catalog_Model_Session');
        $this->renderLayout();
    }

    /**
     * Load specific layout handles by product type id
     *
     */
    protected function _initProductLayout($product)
    {
        $update = $this->getLayout()->getUpdate();
        $this->addPageLayoutHandles(
            array('id' => $product->getId(), 'sku' => $product->getSku(), 'type' => $product->getTypeId())
        );

        if ($product->getPageLayout()) {
            $this->getLayout()->helper('Mage_Page_Helper_Layout')
                ->applyHandle($product->getPageLayout());
        }
        $this->loadLayoutUpdates();

        if ($product->getPageLayout()) {
            $this->getLayout()->helper('Mage_Page_Helper_Layout')
                ->applyTemplate($product->getPageLayout());
        }
        $update->addUpdate($product->getCustomLayoutUpdate());
        $this->generateLayoutXml()->generateLayoutBlocks();
    }
}

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
 * @package     Magento_Review
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Review controller
 *
 * @category   Magento
 * @package    Magento_Review
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Review\Controller;

class Product extends \Magento\Core\Controller\Front\Action
{
    /**
     * Action list where need check enabled cookie
     *
     * @var array
     */
    protected $_cookieCheckActions = array('post');

    /**
     * Core registry
     *
     * @var \Magento\Core\Model\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\UrlInterface
     */
    protected $_urlModel;

    /**
     * @var \Magento\Review\Model\Session
     */
    protected $_reviewSession;

    /**
     * @var \Magento\Core\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $_categoryFactory;

    /**
     * @var \Magento\Core\Model\Logger
     */
    protected $_logger;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * @var \Magento\Review\Model\ReviewFactory
     */
    protected $_reviewFactory;

    /**
     * @var \Magento\Rating\Model\RatingFactory
     */
    protected $_ratingFactory;

    /**
     * @var \Magento\Core\Model\Session
     */
    protected $_session;

    /**
     * @var \Magento\Catalog\Model\Design
     */
    protected $_catalogDesign;

    /**
     * @param \Magento\Core\Controller\Varien\Action\Context $context
     * @param \Magento\Core\Model\Registry $coreRegistry
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\UrlInterface $urlModel
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     * @param \Magento\Core\Model\Logger $logger
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Review\Model\ReviewFactory $reviewFactory
     * @param \Magento\Rating\Model\RatingFactory $ratingFactory
     * @param \Magento\Core\Model\Session $session
     * @param \Magento\Catalog\Model\Design $catalogDesign
     * @param \Magento\Core\Model\Session\Generic $reviewSession
     */
    public function __construct(
        \Magento\Core\Controller\Varien\Action\Context $context,
        \Magento\Core\Model\Registry $coreRegistry,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\UrlInterface $urlModel,
        \Magento\Core\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Core\Model\Logger $logger,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Review\Model\ReviewFactory $reviewFactory,
        \Magento\Rating\Model\RatingFactory $ratingFactory,
        \Magento\Core\Model\Session $session,
        \Magento\Catalog\Model\Design $catalogDesign,
        \Magento\Core\Model\Session\Generic $reviewSession
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_customerSession = $customerSession;
        $this->_urlModel = $urlModel;
        $this->_reviewSession = $reviewSession;
        $this->_storeManager = $storeManager;
        $this->_categoryFactory = $categoryFactory;
        $this->_logger = $logger;
        $this->_productFactory = $productFactory;
        $this->_reviewFactory = $reviewFactory;
        $this->_ratingFactory = $ratingFactory;
        $this->_session = $session;
        $this->_catalogDesign = $catalogDesign;

        parent::__construct($context);
    }

    public function preDispatch()
    {
        parent::preDispatch();

        $allowGuest = $this->_objectManager->get('Magento\Review\Helper\Data')->getIsGuestAllowToWrite();
        if (!$this->getRequest()->isDispatched()) {
            return;
        }

        $action = $this->getRequest()->getActionName();
        if (!$allowGuest && $action == 'post' && $this->getRequest()->isPost()) {
            if (!$this->_customerSession->isLoggedIn()) {
                $this->setFlag('', self::FLAG_NO_DISPATCH, true);
                $this->_customerSession->setBeforeAuthUrl($this->_urlModel->getUrl('*/*/*', array('_current' => true)));
                $this->_reviewSession
                    ->setFormData($this->getRequest()->getPost())
                    ->setRedirectUrl($this->_getRefererUrl());
                $this->_redirectUrl($this->_objectManager->get('Magento\Customer\Helper\Data')->getLoginUrl());
            }
        }

        return $this;
    }
    /**
     * Initialize and check product
     *
     * @return \Magento\Catalog\Model\Product
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
            $category = $this->_categoryFactory->create()->load($categoryId);
            $this->_coreRegistry->register('current_category', $category);
        }

        try {
            $this->_eventManager->dispatch('review_controller_product_init', array('product'=>$product));
            $this->_eventManager->dispatch('review_controller_product_init_after', array(
                'product'           => $product,
                'controller_action' => $this
            ));
        } catch (\Magento\Core\Exception $e) {
            $this->_logger->logException($e);
            return false;
        }

        return $product;
    }

    /**
     * Load product model with data by passed id.
     * Return false if product was not loaded or has incorrect status.
     *
     * @param int $productId
     * @return bool|\Magento\Catalog\Model\Product
     */
    protected function _loadProduct($productId)
    {
        if (!$productId) {
            return false;
        }

        $product = $this->_productFactory->create()
            ->setStoreId($this->_storeManager->getStore()->getId())
            ->load($productId);
        /* @var $product \Magento\Catalog\Model\Product */
        if (!$product->getId() || !$product->isVisibleInCatalog() || !$product->isVisibleInSiteVisibility()) {
            return false;
        }

        $this->_coreRegistry->register('current_product', $product);
        $this->_coreRegistry->register('product', $product);

        return $product;
    }

    /**
     * Load review model with data by passed id.
     * Return false if review was not loaded or review is not approved.
     *
     * @param $reviewId
     * @return bool|\Magento\Review\Model\Review
     */
    protected function _loadReview($reviewId)
    {
        if (!$reviewId) {
            return false;
        }

        $review = $this->_reviewFactory->create()->load($reviewId);
        /* @var $review \Magento\Review\Model\Review */
        if (!$review->getId() || !$review->isApproved() || !$review->isAvailableOnStore($this->_storeManager->getStore())) {
            return false;
        }

        $this->_coreRegistry->register('current_review', $review);

        return $review;
    }

    /**
     * Submit new review action
     */
    public function postAction()
    {
        $data = $this->_reviewSession->getFormData(true);
        if ($data) {
            $rating = array();
            if (isset($data['ratings']) && is_array($data['ratings'])) {
                $rating = $data['ratings'];
            }
        } else {
            $data   = $this->getRequest()->getPost();
            $rating = $this->getRequest()->getParam('ratings', array());
        }

        if (($product = $this->_initProduct()) && !empty($data)) {
            $session    = $this->_session;
            /* @var $session \Magento\Core\Model\Session */
            $review     = $this->_reviewFactory->create()->setData($data);
            /* @var $review \Magento\Review\Model\Review */

            $validate = $review->validate();
            if ($validate === true) {
                try {
                    $review->setEntityId($review->getEntityIdByCode(\Magento\Review\Model\Review::ENTITY_PRODUCT_CODE))
                        ->setEntityPkValue($product->getId())
                        ->setStatusId(\Magento\Review\Model\Review::STATUS_PENDING)
                        ->setCustomerId($this->_customerSession->getCustomerId())
                        ->setStoreId($this->_storeManager->getStore()->getId())
                        ->setStores(array($this->_storeManager->getStore()->getId()))
                        ->save();

                    foreach ($rating as $ratingId => $optionId) {
                        $this->_ratingFactory->create()
                        ->setRatingId($ratingId)
                        ->setReviewId($review->getId())
                        ->setCustomerId($this->_customerSession->getCustomerId())
                        ->addOptionVote($optionId, $product->getId());
                    }

                    $review->aggregate();
                    $session->addSuccess(__('Your review has been accepted for moderation.'));
                } catch (\Exception $e) {
                    $session->setFormData($data);
                    $session->addError(__('We cannot post the review.'));
                }
            } else {
                $session->setFormData($data);
                if (is_array($validate)) {
                    foreach ($validate as $errorMessage) {
                        $session->addError($errorMessage);
                    }
                } else {
                    $session->addError(__('We cannot post the review.'));
                }
            }
        }

        $redirectUrl = $this->_reviewSession->getRedirectUrl(true);
        if ($redirectUrl) {
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
        $product = $this->_initProduct();
        if ($product) {
            $this->_coreRegistry->register('productId', $product->getId());

            $design = $this->_catalogDesign;
            $settings = $design->getDesignSettings($product);
            if ($settings->getCustomDesign()) {
                $design->applyCustomDesign($settings->getCustomDesign());
            }
            $this->_initProductLayout($product);

            // update breadcrumbs
            $breadcrumbsBlock = $this->getLayout()->getBlock('breadcrumbs');
            if ($breadcrumbsBlock) {
                $breadcrumbsBlock->addCrumb('product', array(
                    'label'    => $product->getName(),
                    'link'     => $product->getProductUrl(),
                    'readonly' => true,
                ));
                $breadcrumbsBlock->addCrumb('reviews', array('label' => __('Product Reviews')));
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
        $this->_initLayoutMessages('Magento\Review\Model\Session');
        $this->_initLayoutMessages('Magento\Catalog\Model\Session');
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
            $this->_objectManager->get('Magento\Page\Helper\Layout')
                ->applyHandle($product->getPageLayout());
        }
        $this->loadLayoutUpdates();

        if ($product->getPageLayout()) {
            $this->_objectManager->get('Magento\Page\Helper\Layout')
                ->applyTemplate($product->getPageLayout());
        }
        $update->addUpdate($product->getCustomLayoutUpdate());
        $this->generateLayoutXml()->generateLayoutBlocks();
    }
}

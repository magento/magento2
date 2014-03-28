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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Review\Controller;

use Magento\App\RequestInterface;
use Magento\Catalog\Model\Product as CatalogProduct;
use Magento\Review\Model\Review;

/**
 * Review controller
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Product extends \Magento\App\Action\Action
{
    /**
     * Core registry
     *
     * @var \Magento\Registry
     */
    protected $_coreRegistry = null;

    /**
     * Customer session model
     *
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * Generic session
     *
     * @var \Magento\Session\Generic
     */
    protected $_reviewSession;

    /**
     * Catalog catgory model
     *
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $_categoryFactory;

    /**
     * Logger
     *
     * @var \Magento\Logger
     */
    protected $_logger;

    /**
     * Catalog product model
     *
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * Review model
     *
     * @var \Magento\Review\Model\ReviewFactory
     */
    protected $_reviewFactory;

    /**
     * Rating model
     *
     * @var \Magento\Rating\Model\RatingFactory
     */
    protected $_ratingFactory;

    /**
     * Core session model
     *
     * @var \Magento\Core\Model\Session
     */
    protected $_session;

    /**
     * Catalog design model
     *
     * @var \Magento\Catalog\Model\Design
     */
    protected $_catalogDesign;

    /**
     * Core model store manager interface
     *
     * @var \Magento\Core\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Core form key validator
     *
     * @var \Magento\Core\App\Action\FormKeyValidator
     */
    protected $_formKeyValidator;

    /**
     * @param \Magento\App\Action\Context $context
     * @param \Magento\Registry $coreRegistry
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     * @param \Magento\Logger $logger
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Review\Model\ReviewFactory $reviewFactory
     * @param \Magento\Rating\Model\RatingFactory $ratingFactory
     * @param \Magento\Core\Model\Session $session
     * @param \Magento\Catalog\Model\Design $catalogDesign
     * @param \Magento\Session\Generic $reviewSession
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     * @param \Magento\Core\App\Action\FormKeyValidator $formKeyValidator
     */
    public function __construct(
        \Magento\App\Action\Context $context,
        \Magento\Registry $coreRegistry,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Logger $logger,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Review\Model\ReviewFactory $reviewFactory,
        \Magento\Rating\Model\RatingFactory $ratingFactory,
        \Magento\Core\Model\Session $session,
        \Magento\Catalog\Model\Design $catalogDesign,
        \Magento\Session\Generic $reviewSession,
        \Magento\Core\Model\StoreManagerInterface $storeManager,
        \Magento\Core\App\Action\FormKeyValidator $formKeyValidator
    ) {
        $this->_storeManager = $storeManager;
        $this->_coreRegistry = $coreRegistry;
        $this->_customerSession = $customerSession;
        $this->_reviewSession = $reviewSession;
        $this->_categoryFactory = $categoryFactory;
        $this->_logger = $logger;
        $this->_productFactory = $productFactory;
        $this->_reviewFactory = $reviewFactory;
        $this->_ratingFactory = $ratingFactory;
        $this->_session = $session;
        $this->_catalogDesign = $catalogDesign;
        $this->_formKeyValidator = $formKeyValidator;

        parent::__construct($context);
    }

    /**
     * Dispatch request
     *
     * @param RequestInterface $request
     * @return \Magento\App\ResponseInterface
     */
    public function dispatch(RequestInterface $request)
    {
        $allowGuest = $this->_objectManager->get('Magento\Review\Helper\Data')->getIsGuestAllowToWrite();
        if (!$request->isDispatched()) {
            return parent::dispatch($request);
        }

        if (!$allowGuest && $request->getActionName() == 'post' && $request->isPost()) {
            if (!$this->_customerSession->isLoggedIn()) {
                $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
                $this->_customerSession->setBeforeAuthUrl($this->_url->getUrl('*/*/*', array('_current' => true)));
                $this->_reviewSession->setFormData(
                    $request->getPost()
                )->setRedirectUrl(
                    $this->_redirect->getRefererUrl()
                );
                $this->getResponse()->setRedirect(
                    $this->_objectManager->get('Magento\Customer\Helper\Data')->getLoginUrl()
                );
            }
        }

        return parent::dispatch($request);
    }

    /**
     * Initialize and check product
     *
     * @return CatalogProduct
     */
    protected function _initProduct()
    {
        $this->_eventManager->dispatch('review_controller_product_init_before', array('controller_action' => $this));
        $categoryId = (int)$this->getRequest()->getParam('category', false);
        $productId = (int)$this->getRequest()->getParam('id');

        $product = $this->_loadProduct($productId);
        if (!$product) {
            return false;
        }

        if ($categoryId) {
            $category = $this->_categoryFactory->create()->load($categoryId);
            $this->_coreRegistry->register('current_category', $category);
        }

        try {
            $this->_eventManager->dispatch('review_controller_product_init', array('product' => $product));
            $this->_eventManager->dispatch(
                'review_controller_product_init_after',
                array('product' => $product, 'controller_action' => $this)
            );
        } catch (\Magento\Model\Exception $e) {
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
     * @return bool|CatalogProduct
     */
    protected function _loadProduct($productId)
    {
        if (!$productId) {
            return false;
        }

        $product = $this->_productFactory->create()->setStoreId(
            $this->_storeManager->getStore()->getId()
        )->load(
            $productId
        );
        /* @var $product CatalogProduct */
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
     * @param int $reviewId
     * @return bool|Review
     */
    protected function _loadReview($reviewId)
    {
        if (!$reviewId) {
            return false;
        }

        $review = $this->_reviewFactory->create()->load($reviewId);
        /* @var $review Review */
        if (!$review->getId() || !$review->isApproved() || !$review->isAvailableOnStore(
            $this->_storeManager->getStore()
        )
        ) {
            return false;
        }

        $this->_coreRegistry->register('current_review', $review);

        return $review;
    }

    /**
     * Submit new review action
     *
     * @return void
     */
    public function postAction()
    {
        if (!$this->_formKeyValidator->validate($this->getRequest())) {
            $this->getResponse()->setRedirect($this->_redirect->getRefererUrl());
            return;
        }

        $data = $this->_reviewSession->getFormData(true);
        if ($data) {
            $rating = array();
            if (isset($data['ratings']) && is_array($data['ratings'])) {
                $rating = $data['ratings'];
            }
        } else {
            $data = $this->getRequest()->getPost();
            $rating = $this->getRequest()->getParam('ratings', array());
        }

        if (($product = $this->_initProduct()) && !empty($data)) {
            $session = $this->_session;
            /* @var $session \Magento\Core\Model\Session */
            $review = $this->_reviewFactory->create()->setData($data);
            /* @var $review Review */

            $validate = $review->validate();
            if ($validate === true) {
                try {
                    $review->setEntityId(
                        $review->getEntityIdByCode(Review::ENTITY_PRODUCT_CODE)
                    )->setEntityPkValue(
                        $product->getId()
                    )->setStatusId(
                        Review::STATUS_PENDING
                    )->setCustomerId(
                        $this->_customerSession->getCustomerId()
                    )->setStoreId(
                        $this->_storeManager->getStore()->getId()
                    )->setStores(
                        array($this->_storeManager->getStore()->getId())
                    )->save();

                    foreach ($rating as $ratingId => $optionId) {
                        $this->_ratingFactory->create()->setRatingId(
                            $ratingId
                        )->setReviewId(
                            $review->getId()
                        )->setCustomerId(
                            $this->_customerSession->getCustomerId()
                        )->addOptionVote(
                            $optionId,
                            $product->getId()
                        );
                    }

                    $review->aggregate();
                    $this->messageManager->addSuccess(__('Your review has been accepted for moderation.'));
                } catch (\Exception $e) {
                    $session->setFormData($data);
                    $this->messageManager->addError(__('We cannot post the review.'));
                }
            } else {
                $session->setFormData($data);
                if (is_array($validate)) {
                    foreach ($validate as $errorMessage) {
                        $this->messageManager->addError($errorMessage);
                    }
                } else {
                    $this->messageManager->addError(__('We cannot post the review.'));
                }
            }
        }

        $redirectUrl = $this->_reviewSession->getRedirectUrl(true);
        if ($redirectUrl) {
            $this->getResponse()->setRedirect($redirectUrl);
            return;
        }
        $this->getResponse()->setRedirect($this->_redirect->getRedirectUrl());
    }

    /**
     * Show list of product's reviews
     *
     * @return void
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
            $breadcrumbsBlock = $this->_view->getLayout()->getBlock('breadcrumbs');
            if ($breadcrumbsBlock) {
                $breadcrumbsBlock->addCrumb(
                    'product',
                    array('label' => $product->getName(), 'link' => $product->getProductUrl(), 'readonly' => true)
                );
                $breadcrumbsBlock->addCrumb('reviews', array('label' => __('Product Reviews')));
            }

            $this->_view->renderLayout();
        } elseif (!$this->getResponse()->isRedirect()) {
            $this->_forward('noroute');
        }
    }

    /**
     * Show details of one review
     *
     * @return void
     */
    public function viewAction()
    {
        $review = $this->_loadReview((int)$this->getRequest()->getParam('id'));
        if (!$review) {
            $this->_forward('noroute');
            return;
        }

        $product = $this->_loadProduct($review->getEntityPkValue());
        if (!$product) {
            $this->_forward('noroute');
            return;
        }

        $this->_view->loadLayout();
        $this->_view->getLayout()->initMessages();
        $this->_view->renderLayout();
    }

    /**
     * Load specific layout handles by product type id
     *
     * @param Product $product
     * @return void
     */
    protected function _initProductLayout($product)
    {
        $update = $this->_view->getLayout()->getUpdate();
        $update->addHandle('default');
        $this->_view->addPageLayoutHandles(
            array('id' => $product->getId(), 'sku' => $product->getSku(), 'type' => $product->getTypeId())
        );

        if ($product->getPageLayout()) {
            $this->_objectManager->get('Magento\Theme\Helper\Layout')->applyHandle($product->getPageLayout());
        }
        $this->_view->loadLayoutUpdates();

        if ($product->getPageLayout()) {
            $this->_objectManager->get('Magento\Theme\Helper\Layout')->applyTemplate($product->getPageLayout());
        }
        $update->addUpdate($product->getCustomLayoutUpdate());
        $this->_view->generateLayoutXml();
        $this->_view->generateLayoutBlocks();
    }
}

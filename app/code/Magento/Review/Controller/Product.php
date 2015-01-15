<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Review\Controller;

use Magento\Catalog\Model\Product as CatalogProduct;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Review\Model\Review;

/**
 * Review controller
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Product extends \Magento\Framework\App\Action\Action
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
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
     * @var \Magento\Framework\Session\Generic
     */
    protected $_reviewSession;

    /**
     * Catalog catgory model
     *
     * @var \Magento\Catalog\Api\CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * Logger
     *
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;

    /**
     * Catalog product model
     *
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * Review model
     *
     * @var \Magento\Review\Model\ReviewFactory
     */
    protected $_reviewFactory;

    /**
     * Rating model
     *
     * @var \Magento\Review\Model\RatingFactory
     */
    protected $_ratingFactory;

    /**
     * Catalog design model
     *
     * @var \Magento\Catalog\Model\Design
     */
    protected $_catalogDesign;

    /**
     * Core model store manager interface
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Core form key validator
     *
     * @var \Magento\Core\App\Action\FormKeyValidator
     */
    protected $_formKeyValidator;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Review\Model\ReviewFactory $reviewFactory
     * @param \Magento\Review\Model\RatingFactory $ratingFactory
     * @param \Magento\Catalog\Model\Design $catalogDesign
     * @param \Magento\Framework\Session\Generic $reviewSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Core\App\Action\FormKeyValidator $formKeyValidator
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Review\Model\ReviewFactory $reviewFactory,
        \Magento\Review\Model\RatingFactory $ratingFactory,
        \Magento\Catalog\Model\Design $catalogDesign,
        \Magento\Framework\Session\Generic $reviewSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Core\App\Action\FormKeyValidator $formKeyValidator
    ) {
        $this->_storeManager = $storeManager;
        $this->_coreRegistry = $coreRegistry;
        $this->_customerSession = $customerSession;
        $this->_reviewSession = $reviewSession;
        $this->categoryRepository = $categoryRepository;
        $this->_logger = $logger;
        $this->productRepository = $productRepository;
        $this->_reviewFactory = $reviewFactory;
        $this->_ratingFactory = $ratingFactory;
        $this->_catalogDesign = $catalogDesign;
        $this->_formKeyValidator = $formKeyValidator;

        parent::__construct($context);
    }

    /**
     * Dispatch request
     *
     * @param RequestInterface $request
     * @return \Magento\Framework\App\ResponseInterface
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
                $this->_customerSession->setBeforeAuthUrl($this->_url->getUrl('*/*/*', ['_current' => true]));
                $this->_reviewSession->setFormData(
                    $request->getPost()
                )->setRedirectUrl(
                    $this->_redirect->getRefererUrl()
                );
                $this->getResponse()->setRedirect(
                    $this->_objectManager->get('Magento\Customer\Model\Url')->getLoginUrl()
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
        $this->_eventManager->dispatch('review_controller_product_init_before', ['controller_action' => $this]);
        $categoryId = (int)$this->getRequest()->getParam('category', false);
        $productId = (int)$this->getRequest()->getParam('id');

        $product = $this->_loadProduct($productId);
        if (!$product) {
            return false;
        }

        if ($categoryId) {
            $category = $this->categoryRepository->get($categoryId);
            $this->_coreRegistry->register('current_category', $category);
        }

        try {
            $this->_eventManager->dispatch('review_controller_product_init', ['product' => $product]);
            $this->_eventManager->dispatch(
                'review_controller_product_init_after',
                ['product' => $product, 'controller_action' => $this]
            );
        } catch (\Magento\Framework\Model\Exception $e) {
            $this->_logger->critical($e);
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

        try {
            $product = $this->productRepository->getById($productId);
            if (!$product->isVisibleInCatalog() || !$product->isVisibleInSiteVisibility()) {
                throw new NoSuchEntityException();
            }
        } catch (NoSuchEntityException $noEntityException) {
            return false;
        }

        $this->_coreRegistry->register('current_product', $product);
        $this->_coreRegistry->register('product', $product);

        return $product;
    }
}

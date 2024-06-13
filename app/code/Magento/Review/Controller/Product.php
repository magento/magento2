<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Review\Controller;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Design;
use Magento\Catalog\Model\Product as CatalogProduct;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\Url;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Registry;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Session\Generic;
use Magento\Review\Helper\Data;
use Magento\Review\Model\RatingFactory;
use Magento\Review\Model\Review;
use Magento\Review\Model\Review\Config as ReviewsConfig;
use Magento\Review\Model\ReviewFactory;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Review controller
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
abstract class Product extends Action
{
    /**
     * Core Registry class
     *
     * @var Registry
     */
    protected $coreRegistry = null;

    /**
     * Customer session model
     *
     * @var Session
     */
    protected $customerSession;

    /**
     * Generic session
     *
     * @var Generic
     */
    protected $reviewSession;

    /**
     * Catalog category model
     *
     * @var CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * Logger for adding logs
     *
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Catalog product model
     *
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * Review model
     *
     * @var ReviewFactory
     */
    protected $reviewFactory;

    /**
     * Rating model
     *
     * @var RatingFactory
     */
    protected $ratingFactory;

    /**
     * Catalog design model
     *
     * @var Design
     */
    protected $catalogDesign;

    /**
     * Core model store manager interface
     *
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Core form key validator
     *
     * @var Validator
     */
    protected $formKeyValidator;

    /**
     * Review config
     *
     * @var ReviewsConfig
     */
    protected $reviewsConfig;

    /**
     * @param Context $context
     * @param Registry $coreRegistry
     * @param Session $customerSession
     * @param CategoryRepositoryInterface $categoryRepository
     * @param LoggerInterface $logger
     * @param ProductRepositoryInterface $productRepository
     * @param ReviewFactory $reviewFactory
     * @param RatingFactory $ratingFactory
     * @param Design $catalogDesign
     * @param Generic $reviewSession
     * @param StoreManagerInterface $storeManager
     * @param Validator $formKeyValidator
     * @param ReviewsConfig $reviewsConfig
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        Session $customerSession,
        CategoryRepositoryInterface $categoryRepository,
        LoggerInterface $logger,
        ProductRepositoryInterface $productRepository,
        ReviewFactory $reviewFactory,
        RatingFactory $ratingFactory,
        Design $catalogDesign,
        Generic $reviewSession,
        StoreManagerInterface $storeManager,
        Validator $formKeyValidator,
        ?ReviewsConfig $reviewsConfig = null
    ) {
        $this->storeManager = $storeManager;
        $this->coreRegistry = $coreRegistry;
        $this->customerSession = $customerSession;
        $this->reviewSession = $reviewSession;
        $this->categoryRepository = $categoryRepository;
        $this->logger = $logger;
        $this->productRepository = $productRepository;
        $this->reviewFactory = $reviewFactory;
        $this->ratingFactory = $ratingFactory;
        $this->catalogDesign = $catalogDesign;
        $this->formKeyValidator = $formKeyValidator;
        $this->reviewsConfig = $reviewsConfig ?: ObjectManager::getInstance()->get(ReviewsConfig::class);
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
        $allowGuest = $this->_objectManager->get(Data::class)->getIsGuestAllowToWrite();
        if (!$request->isDispatched()) {
            return parent::dispatch($request);
        }

        if (!$allowGuest && $request->getActionName() == 'post' && $request->isPost()) {
            if (!$this->customerSession->isLoggedIn()) {
                $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
                $this->customerSession->setBeforeAuthUrl($this->_url->getUrl('*/*/*', ['_current' => true]));
                $this->reviewSession->setFormData(
                    $request->getPostValue()
                )->setRedirectUrl(
                    $this->_redirect->getRefererUrl()
                );
                $this->getResponse()->setRedirect(
                    $this->_objectManager->get(Url::class)->getLoginUrl()
                );
            }
        }

        return parent::dispatch($request);
    }

    /**
     * Initialize and check product
     *
     * @return \Magento\Catalog\Model\Product|bool
     */
    protected function initProduct()
    {
        $this->_eventManager->dispatch('review_controller_product_init_before', ['controller_action' => $this]);
        $categoryId = (int)$this->getRequest()->getParam('category', false);
        $productId = (int)$this->getRequest()->getParam('id');

        $product = $this->loadProduct($productId);
        if (!$product) {
            return false;
        }

        if ($categoryId) {
            $category = $this->categoryRepository->get($categoryId);
            $this->coreRegistry->register('current_category', $category);
        }

        try {
            $this->_eventManager->dispatch('review_controller_product_init', ['product' => $product]);
            $this->_eventManager->dispatch(
                'review_controller_product_init_after',
                ['product' => $product, 'controller_action' => $this]
            );
        } catch (LocalizedException $e) {
            $this->logger->critical($e);
            return false;
        }

        return $product;
    }

    /**
     * Load product model with data by passed id. Return false if product was not loaded or has incorrect status.
     *
     * @param int $productId
     * @return bool|CatalogProduct
     */
    protected function loadProduct($productId)
    {
        if (!$productId) {
            return false;
        }
        try {
            $product = $this->productRepository->getById($productId);

            if ((!in_array($this->storeManager->getStore()->getWebsiteId(), $product->getWebsiteIds()))
                || (!$product->isVisibleInCatalog() || !$product->isVisibleInSiteVisibility())
            ) {
                    return false;
            }
        } catch (NoSuchEntityException $noEntityException) {
            return false;
        }

        $this->coreRegistry->register('current_product', $product);
        $this->coreRegistry->register('product', $product);

        return $product;
    }
}

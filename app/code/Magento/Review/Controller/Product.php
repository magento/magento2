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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Review\Controller;

use Magento\Framework\App\RequestInterface;
use Magento\Catalog\Model\Product as CatalogProduct;
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
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $_categoryFactory;

    /**
     * Logger
     *
     * @var \Magento\Framework\Logger
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
     * @var \Magento\Framework\StoreManagerInterface
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
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     * @param \Magento\Framework\Logger $logger
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Review\Model\ReviewFactory $reviewFactory
     * @param \Magento\Review\Model\RatingFactory $ratingFactory
     * @param \Magento\Catalog\Model\Design $catalogDesign
     * @param \Magento\Framework\Session\Generic $reviewSession
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     * @param \Magento\Core\App\Action\FormKeyValidator $formKeyValidator
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Framework\Logger $logger,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Review\Model\ReviewFactory $reviewFactory,
        \Magento\Review\Model\RatingFactory $ratingFactory,
        \Magento\Catalog\Model\Design $catalogDesign,
        \Magento\Framework\Session\Generic $reviewSession,
        \Magento\Framework\StoreManagerInterface $storeManager,
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
        } catch (\Magento\Framework\Model\Exception $e) {
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
}

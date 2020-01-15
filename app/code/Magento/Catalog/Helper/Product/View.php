<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Helper\Product;

use Magento\Catalog\Controller\Product\View\ViewInterface;
use Magento\Catalog\Helper\Product;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Design;
use Magento\Catalog\Model\Session;
use Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Framework\View\Result\Page as ResultPage;

/**
 * Catalog category helper
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class View extends AbstractHelper
{
    /**
     * List of catalog product session message groups
     *
     * @var array
     */
    protected $messageGroups;

    /**
     * Core registry
     *
     * @var Registry
     */
    protected $_coreRegistry;

    /**
     * Catalog product
     *
     * @var Product
     */
    protected $_catalogProduct;

    /**
     * Catalog design
     *
     * @var Design
     */
    protected $_catalogDesign;

    /**
     * Catalog session
     *
     * @var Session
     */
    protected $_catalogSession;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var CategoryUrlPathGenerator
     */
    protected $categoryUrlPathGenerator;

    /**
     * @var StringUtils
     */
    private $string;

    /**
     * Constructor
     *
     * @param Context $context
     * @param Session $catalogSession
     * @param Design $catalogDesign
     * @param Product $catalogProduct
     * @param Registry $coreRegistry
     * @param ManagerInterface $messageManager
     * @param CategoryUrlPathGenerator $categoryUrlPathGenerator
     * @param StringUtils $string
     * @param array $messageGroups
     */
    public function __construct(
        Context $context,
        Session $catalogSession,
        Design $catalogDesign,
        Product $catalogProduct,
        Registry $coreRegistry,
        ManagerInterface $messageManager,
        CategoryUrlPathGenerator $categoryUrlPathGenerator,
        StringUtils $string,
        array $messageGroups = []
    ) {
        $this->_catalogSession = $catalogSession;
        $this->_catalogDesign = $catalogDesign;
        $this->_catalogProduct = $catalogProduct;
        $this->_coreRegistry = $coreRegistry;
        $this->messageGroups = $messageGroups;
        $this->messageManager = $messageManager;
        $this->categoryUrlPathGenerator = $categoryUrlPathGenerator;
        $this->string = $string;
        parent::__construct($context);
    }

    /**
     * Add meta information from product to layout
     *
     * @param ResultPage $resultPage
     * @param \Magento\Catalog\Model\Product $product
     *
     * @return $this
     */
    private function preparePageMetadata(ResultPage $resultPage, $product)
    {
        $pageLayout = $resultPage->getLayout();
        $pageConfig = $resultPage->getConfig();

        $metaTitle = $product->getMetaTitle();
        $pageConfig->setMetaTitle($metaTitle);
        $pageConfig->getTitle()->set($metaTitle ?: $product->getName());

        $keyword = $product->getMetaKeyword();
        $currentCategory = $this->_coreRegistry->registry('current_category');
        if ($keyword) {
            $pageConfig->setKeywords($keyword);
        } elseif ($currentCategory) {
            $pageConfig->setKeywords($product->getName());
        }

        $description = $product->getMetaDescription();
        if ($description) {
            $pageConfig->setDescription($description);
        } else {
            $pageConfig->setDescription($this->string->substr(strip_tags($product->getDescription()), 0, 255));
        }

        if ($this->_catalogProduct->canUseCanonicalTag()) {
            $pageConfig->addRemotePageAsset(
                $product->getUrlModel()->getUrl($product, ['_ignore_category' => true]),
                'canonical',
                ['attributes' => ['rel' => 'canonical']]
            );
        }

        $pageMainTitle = $pageLayout->getBlock('page.main.title');
        if ($pageMainTitle) {
            $pageMainTitle->setPageTitle($product->getName());
        }

        return $this;
    }

    /**
     * Init layout for viewing product page
     *
     * @param ResultPage $resultPage
     * @param \Magento\Catalog\Model\Product $product
     * @param null|DataObject $params
     *
     * @return View
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function initProductLayout(ResultPage $resultPage, $product, $params = null)
    {
        $settings = $this->_catalogDesign->getDesignSettings($product);
        $pageConfig = $resultPage->getConfig();

        if ($settings->getCustomDesign()) {
            $this->_catalogDesign->applyCustomDesign($settings->getCustomDesign());
        }

        // Apply custom page layout
        if ($settings->getPageLayout()) {
            $pageConfig->setPageLayout($settings->getPageLayout());
        }

        $urlSafeSku = rawurlencode($product->getSku());

        // Load default page handles and page configurations
        if ($params && $params->getBeforeHandles()) {
            foreach ($params->getBeforeHandles() as $handle) {
                $resultPage->addPageLayoutHandles(['type' => $product->getTypeId()], $handle, false);
                $resultPage->addPageLayoutHandles(['id' => $product->getId(), 'sku' => $urlSafeSku], $handle);
            }
        }

        $resultPage->addPageLayoutHandles(['type' => $product->getTypeId()], null, false);
        $resultPage->addPageLayoutHandles(['id' => $product->getId(), 'sku' => $urlSafeSku]);

        if ($params && $params->getAfterHandles()) {
            foreach ($params->getAfterHandles() as $handle) {
                $resultPage->addPageLayoutHandles(['type' => $product->getTypeId()], $handle, false);
                $resultPage->addPageLayoutHandles(['id' => $product->getId(), 'sku' => $urlSafeSku], $handle);
            }
        }

        // Apply custom layout update once layout is loaded
        $update = $resultPage->getLayout()->getUpdate();
        $layoutUpdates = $settings->getLayoutUpdates();
        if ($layoutUpdates) {
            if (is_array($layoutUpdates)) {
                foreach ($layoutUpdates as $layoutUpdate) {
                    $update->addUpdate($layoutUpdate);
                }
            }
        }
        if ($settings->getPageLayoutHandles()) {
            $resultPage->addPageLayoutHandles($settings->getPageLayoutHandles());
        }

        $currentCategory = $this->_coreRegistry->registry('current_category');
        $controllerClass = $this->_request->getFullActionName();
        if ($controllerClass != 'catalog-product-view') {
            $pageConfig->addBodyClass('catalog-product-view');
        }
        $pageConfig->addBodyClass('product-' . $product->getUrlKey());
        if ($currentCategory instanceof Category) {
            $pageConfig->addBodyClass(
                'categorypath-' . $this->categoryUrlPathGenerator->getUrlPath($currentCategory)
            )
                ->addBodyClass('category-' . $currentCategory->getUrlKey());
        }

        return $this;
    }

    /**
     * Prepares product view page - inits layout and all needed stuff
     *
     * $params can have all values as $params in \Magento\Catalog\Helper\Product - initProduct().
     * Plus following keys:
     *   - 'buy_request' - \Magento\Framework\DataObject holding buyRequest to configure product
     *   - 'specify_options' - boolean, whether to show 'Specify options' message
     *   - 'configure_mode' - boolean, whether we're in Configure-mode to edit product configuration
     *
     * @param ResultPage $resultPage
     * @param int $productId
     * @param Action $controller
     * @param null|DataObject $params
     *
     * @return View
     *
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function prepareAndRender(ResultPage $resultPage, $productId, $controller, $params = null)
    {
        /**
         * Remove default action handle from layout update to avoid its usage during processing of another action,
         * It is possible that forwarding to another action occurs, e.g. to 'noroute'.
         * Default action handle is restored just before the end of current method.
         */
        $defaultActionHandle = $resultPage->getDefaultLayoutHandle();
        $handles = $resultPage->getLayout()->getUpdate()->getHandles();
        if (in_array($defaultActionHandle, $handles)) {
            $resultPage->getLayout()->getUpdate()->removeHandle($resultPage->getDefaultLayoutHandle());
        }

        if (!$controller instanceof ViewInterface) {
            throw new LocalizedException(
                __('Bad controller interface for showing product')
            );
        }
        // Prepare data
        $productHelper = $this->_catalogProduct;
        if (!$params) {
            $params = new DataObject();
        }

        // Standard algorithm to prepare and render product view page
        $product = $productHelper->initProduct($productId, $controller, $params);
        if (!$product) {
            throw new NoSuchEntityException(__('Product is not loaded'));
        }

        $buyRequest = $params->getBuyRequest();
        if ($buyRequest) {
            $productHelper->prepareProductOptions($product, $buyRequest);
        }

        if ($params->hasConfigureMode()) {
            $product->setConfigureMode($params->getConfigureMode());
        }

        $this->_eventManager->dispatch('catalog_controller_product_view', ['product' => $product]);

        $this->_catalogSession->setLastViewedProductId($product->getId());

        if (in_array($defaultActionHandle, $handles)) {
            $resultPage->addDefaultHandle();
        }

        $this->initProductLayout($resultPage, $product, $params);
        $this->preparePageMetadata($resultPage, $product);
        return $this;
    }
}

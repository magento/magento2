<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Block\Product;

/**
 * Constructor modification point for Magento\Catalog\Block\Product\AbstractProduct.
 *
 * All context classes were introduced to allow for backwards compatible constructor modifications
 * of classes that were supposed to be extended by extension developers.
 *
 * Do not call methods of this class directly.
 *
 * As Magento moves from inheritance-based APIs all such classes will be deprecated together with
 * the classes they were introduced for.
 *
 * @deprecated 2.2.0
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 2.0.0
 */
class Context extends \Magento\Framework\View\Element\Template\Context
{
    /**
     * @var \Magento\Catalog\Helper\Image
     * @since 2.0.0
     */
    protected $imageHelper;

    /**
     * @var \Magento\Catalog\Helper\Product\Compare
     * @since 2.0.0
     */
    protected $compareProduct;

    /**
     * @var \Magento\Wishlist\Helper\Data
     * @since 2.0.0
     */
    protected $wishlistHelper;

    /**
     * @var \Magento\Checkout\Helper\Cart
     * @since 2.0.0
     */
    protected $cartHelper;

    /**
     * @var \Magento\Catalog\Model\Config
     * @since 2.0.0
     */
    protected $catalogConfig;

    /**
     * @var \Magento\Framework\Registry
     * @since 2.0.0
     */
    protected $registry;

    /**
     * @var \Magento\Tax\Helper\Data
     * @since 2.0.0
     */
    protected $taxData;

    /**
     * @var \Magento\Catalog\Helper\Data
     * @since 2.0.0
     */
    protected $catalogHelper;

    /**
     * @var \Magento\Framework\Math\Random
     * @since 2.0.0
     */
    protected $mathRandom;

    /**
     * @var ReviewRendererInterface
     * @since 2.0.0
     */
    protected $reviewRenderer;

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     * @since 2.0.0
     */
    protected $stockRegistry;

    /**
     * @var \Magento\Framework\View\Page\Config
     * @since 2.0.0
     */
    protected $pageConfig;

    /**
     * @var ImageBuilder
     * @since 2.0.0
     */
    protected $imageBuilder;

    /**
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\View\LayoutInterface $layout
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Framework\App\CacheInterface $cache
     * @param \Magento\Framework\View\DesignInterface $design
     * @param \Magento\Framework\Session\SessionManagerInterface $session
     * @param \Magento\Framework\Session\SidResolverInterface $sidResolver
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     * @param \Magento\Framework\View\ConfigInterface $viewConfig
     * @param \Magento\Framework\App\Cache\StateInterface $cacheState
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Escaper $escaper
     * @param \Magento\Framework\Filter\FilterManager $filterManager
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\View\FileSystem $viewFileSystem
     * @param \Magento\Framework\View\TemplateEnginePool $enginePool
     * @param \Magento\Framework\App\State $appState
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\View\Page\Config $pageConfig
     * @param \Magento\Framework\View\Element\Template\File\Resolver $resolver
     * @param \Magento\Framework\View\Element\Template\File\Validator $validator
     * @param \Magento\Catalog\Model\Config $catalogConfig
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Tax\Helper\Data $taxHelper
     * @param \Magento\Catalog\Helper\Data $catalogHelper
     * @param \Magento\Framework\Math\Random $mathRandom
     * @param \Magento\Checkout\Helper\Cart $cartHelper
     * @param \Magento\Wishlist\Helper\Data $wishlistHelper
     * @param \Magento\Catalog\Helper\Product\Compare $compareProduct
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param ImageBuilder $imageBuilder
     * @param ReviewRendererInterface $reviewRenderer
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\View\LayoutInterface $layout,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\App\CacheInterface $cache,
        \Magento\Framework\View\DesignInterface $design,
        \Magento\Framework\Session\SessionManagerInterface $session,
        \Magento\Framework\Session\SidResolverInterface $sidResolver,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Framework\View\ConfigInterface $viewConfig,
        \Magento\Framework\App\Cache\StateInterface $cacheState,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Escaper $escaper,
        \Magento\Framework\Filter\FilterManager $filterManager,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\View\FileSystem $viewFileSystem,
        \Magento\Framework\View\TemplateEnginePool $enginePool,
        \Magento\Framework\App\State $appState,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\View\Page\Config $pageConfig,
        \Magento\Framework\View\Element\Template\File\Resolver $resolver,
        \Magento\Framework\View\Element\Template\File\Validator $validator,
        \Magento\Catalog\Model\Config $catalogConfig,
        \Magento\Framework\Registry $registry,
        \Magento\Tax\Helper\Data $taxHelper,
        \Magento\Catalog\Helper\Data $catalogHelper,
        \Magento\Framework\Math\Random $mathRandom,
        \Magento\Checkout\Helper\Cart $cartHelper,
        \Magento\Wishlist\Helper\Data $wishlistHelper,
        \Magento\Catalog\Helper\Product\Compare $compareProduct,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Catalog\Block\Product\ImageBuilder $imageBuilder,
        ReviewRendererInterface $reviewRenderer,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
    ) {
        $this->imageHelper = $imageHelper;
        $this->imageBuilder = $imageBuilder;
        $this->compareProduct = $compareProduct;
        $this->wishlistHelper = $wishlistHelper;
        $this->cartHelper = $cartHelper;
        $this->catalogConfig = $catalogConfig;
        $this->registry = $registry;
        $this->taxData = $taxHelper;
        $this->catalogHelper = $catalogHelper;
        $this->mathRandom = $mathRandom;
        $this->reviewRenderer = $reviewRenderer;
        $this->stockRegistry = $stockRegistry;
        parent::__construct(
            $request,
            $layout,
            $eventManager,
            $urlBuilder,
            $cache,
            $design,
            $session,
            $sidResolver,
            $scopeConfig,
            $assetRepo,
            $viewConfig,
            $cacheState,
            $logger,
            $escaper,
            $filterManager,
            $localeDate,
            $inlineTranslation,
            $filesystem,
            $viewFileSystem,
            $enginePool,
            $appState,
            $storeManager,
            $pageConfig,
            $resolver,
            $validator
        );
    }

    /**
     * @return \Magento\CatalogInventory\Api\StockRegistryInterface
     * @since 2.0.0
     */
    public function getStockRegistry()
    {
        return $this->stockRegistry;
    }

    /**
     * @return \Magento\Checkout\Helper\Cart
     * @since 2.0.0
     */
    public function getCartHelper()
    {
        return $this->cartHelper;
    }

    /**
     * @return \Magento\Catalog\Model\Config
     * @since 2.0.0
     */
    public function getCatalogConfig()
    {
        return $this->catalogConfig;
    }

    /**
     * @return \Magento\Catalog\Helper\Data
     * @since 2.0.0
     */
    public function getCatalogHelper()
    {
        return $this->catalogHelper;
    }

    /**
     * @return \Magento\Catalog\Helper\Product\Compare
     * @since 2.0.0
     */
    public function getCompareProduct()
    {
        return $this->compareProduct;
    }

    /**
     * @return \Magento\Catalog\Helper\Image
     * @since 2.0.0
     */
    public function getImageHelper()
    {
        return $this->imageHelper;
    }

    /**
     * @return \Magento\Catalog\Block\Product\ImageBuilder
     * @since 2.0.0
     */
    public function getImageBuilder()
    {
        return $this->imageBuilder;
    }

    /**
     * @return \Magento\Framework\Math\Random
     * @since 2.0.0
     */
    public function getMathRandom()
    {
        return $this->mathRandom;
    }

    /**
     * @return \Magento\Framework\Registry
     * @since 2.0.0
     */
    public function getRegistry()
    {
        return $this->registry;
    }

    /**
     * @return \Magento\Tax\Helper\Data
     * @since 2.0.0
     */
    public function getTaxData()
    {
        return $this->taxData;
    }

    /**
     * @return \Magento\Wishlist\Helper\Data
     * @since 2.0.0
     */
    public function getWishlistHelper()
    {
        return $this->wishlistHelper;
    }

    /**
     * @return \Magento\Catalog\Block\Product\ReviewRendererInterface
     * @since 2.0.0
     */
    public function getReviewRenderer()
    {
        return $this->reviewRenderer;
    }
}

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
namespace Magento\Catalog\Block\Product;

class Context extends \Magento\View\Element\Template\Context
{
    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $imageHelper;

    /**
     * @var \Magento\Theme\Helper\Layout
     */
    protected $layoutHelper;

    /**
     * @var \Magento\Catalog\Helper\Product\Compare
     */
    protected $compareProduct;

    /**
     * @var \Magento\Wishlist\Helper\Data
     */
    protected $wishlistHelper;

    /**
     * @var \Magento\Checkout\Helper\Cart
     */
    protected $cartHelper;

    /**
     * @var \Magento\Catalog\Model\Config
     */
    protected $catalogConfig;

    /**
     * @var \Magento\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Tax\Helper\Data
     */
    protected $taxData;

    /**
     * @var \Magento\Catalog\Helper\Data
     */
    protected $catalogHelper;

    /**
     * @var \Magento\Math\Random
     */
    protected $mathRandom;

    /**
     * @var ReviewRendererInterface
     */
    protected $reviewRenderer;

    /**
     * @param \Magento\App\RequestInterface $request
     * @param \Magento\View\LayoutInterface $layout
     * @param \Magento\Event\ManagerInterface $eventManager
     * @param \Magento\UrlInterface $urlBuilder
     * @param \Magento\TranslateInterface $translator
     * @param \Magento\App\CacheInterface $cache
     * @param \Magento\View\DesignInterface $design
     * @param \Magento\Session\SessionManagerInterface $session
     * @param \Magento\Session\SidResolverInterface $sidResolver
     * @param \Magento\Core\Model\Store\Config $storeConfig
     * @param \Magento\View\Url $viewUrl
     * @param \Magento\View\ConfigInterface $viewConfig
     * @param \Magento\App\Cache\StateInterface $cacheState
     * @param \Magento\Logger $logger
     * @param \Magento\Escaper $escaper
     * @param \Magento\Filter\FilterManager $filterManager
     * @param \Magento\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Translate\Inline\StateInterface $inlineTranslation
     * @param \Magento\App\Filesystem $filesystem
     * @param \Magento\View\FileSystem $viewFileSystem
     * @param \Magento\View\TemplateEnginePool $enginePool
     * @param \Magento\App\State $appState
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\Config $catalogConfig
     * @param \Magento\Registry $registry
     * @param \Magento\Tax\Helper\Data $taxHelper
     * @param \Magento\Catalog\Helper\Data $catalogHelper
     * @param \Magento\Math\Random $mathRandom
     * @param \Magento\Checkout\Helper\Cart $cartHelper
     * @param \Magento\Wishlist\Helper\Data $wishlistHelper
     * @param \Magento\Catalog\Helper\Product\Compare $compareProduct
     * @param \Magento\Theme\Helper\Layout $layoutHelper
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param ReviewRendererInterface $reviewRenderer
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\App\RequestInterface $request,
        \Magento\View\LayoutInterface $layout,
        \Magento\Event\ManagerInterface $eventManager,
        \Magento\UrlInterface $urlBuilder,
        \Magento\TranslateInterface $translator,
        \Magento\App\CacheInterface $cache,
        \Magento\View\DesignInterface $design,
        \Magento\Session\SessionManagerInterface $session,
        \Magento\Session\SidResolverInterface $sidResolver,
        \Magento\Core\Model\Store\Config $storeConfig,
        \Magento\View\Url $viewUrl,
        \Magento\View\ConfigInterface $viewConfig,
        \Magento\App\Cache\StateInterface $cacheState,
        \Magento\Logger $logger,
        \Magento\Escaper $escaper,
        \Magento\Filter\FilterManager $filterManager,
        \Magento\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\App\Filesystem $filesystem,
        \Magento\View\FileSystem $viewFileSystem,
        \Magento\View\TemplateEnginePool $enginePool,
        \Magento\App\State $appState,
        \Magento\Core\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Config $catalogConfig,
        \Magento\Registry $registry,
        \Magento\Tax\Helper\Data $taxHelper,
        \Magento\Catalog\Helper\Data $catalogHelper,
        \Magento\Math\Random $mathRandom,
        \Magento\Checkout\Helper\Cart $cartHelper,
        \Magento\Wishlist\Helper\Data $wishlistHelper,
        \Magento\Catalog\Helper\Product\Compare $compareProduct,
        \Magento\Theme\Helper\Layout $layoutHelper,
        \Magento\Catalog\Helper\Image $imageHelper,
        ReviewRendererInterface $reviewRenderer
    ) {
        $this->imageHelper = $imageHelper;
        $this->layoutHelper = $layoutHelper;
        $this->compareProduct = $compareProduct;
        $this->wishlistHelper = $wishlistHelper;
        $this->cartHelper = $cartHelper;
        $this->catalogConfig = $catalogConfig;
        $this->registry = $registry;
        $this->taxData = $taxHelper;
        $this->catalogHelper = $catalogHelper;
        $this->mathRandom = $mathRandom;
        $this->reviewRenderer = $reviewRenderer;
        parent::__construct(
            $request,
            $layout,
            $eventManager,
            $urlBuilder,
            $translator,
            $cache,
            $design,
            $session,
            $sidResolver,
            $storeConfig,
            $viewUrl,
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
            $storeManager
        );
    }

    /**
     * @return \Magento\Checkout\Helper\Cart
     */
    public function getCartHelper()
    {
        return $this->cartHelper;
    }

    /**
     * @return \Magento\Catalog\Model\Config
     */
    public function getCatalogConfig()
    {
        return $this->catalogConfig;
    }

    /**
     * @return \Magento\Catalog\Helper\Data
     */
    public function getCatalogHelper()
    {
        return $this->catalogHelper;
    }

    /**
     * @return \Magento\Catalog\Helper\Product\Compare
     */
    public function getCompareProduct()
    {
        return $this->compareProduct;
    }

    /**
     * @return \Magento\Catalog\Helper\Image
     */
    public function getImageHelper()
    {
        return $this->imageHelper;
    }

    /**
     * @return \Magento\Theme\Helper\Layout
     */
    public function getLayoutHelper()
    {
        return $this->layoutHelper;
    }

    /**
     * @return \Magento\Math\Random
     */
    public function getMathRandom()
    {
        return $this->mathRandom;
    }

    /**
     * @return \Magento\Registry
     */
    public function getRegistry()
    {
        return $this->registry;
    }

    /**
     * @return \Magento\Tax\Helper\Data
     */
    public function getTaxData()
    {
        return $this->taxData;
    }

    /**
     * @return \Magento\Wishlist\Helper\Data
     */
    public function getWishlistHelper()
    {
        return $this->wishlistHelper;
    }

    /**
     * @return \Magento\Catalog\Block\Product\ReviewRendererInterface
     */
    public function getReviewRenderer()
    {
        return $this->reviewRenderer;
    }
}

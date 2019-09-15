<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Controller\Category;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Design;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Catalog\Model\Product\ProductList\ToolbarMemorizer;
use Magento\Catalog\Model\Session;
use Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * View a category on storefront. Needs to be accessible by POST because of the store switching.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class View extends Action implements HttpGetActionInterface, HttpPostActionInterface
{
    /**
     * Core registry
     *
     * @var Registry
     */
    protected $_coreRegistry = null;

    /**
     * Catalog session
     *
     * @var Session
     */
    protected $_catalogSession;

    /**
     * Catalog design
     *
     * @var Design
     */
    protected $_catalogDesign;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var CategoryUrlPathGenerator
     */
    protected $categoryUrlPathGenerator;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * Catalog Layer Resolver
     *
     * @var Resolver
     */
    private $layerResolver;

    /**
     * @var CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * @var ToolbarMemorizer
     */
    private $toolbarMemorizer;

    /**
     * Constructor
     *
     * @param Context $context
     * @param Design $catalogDesign
     * @param Session $catalogSession
     * @param Registry $coreRegistry
     * @param StoreManagerInterface $storeManager
     * @param CategoryUrlPathGenerator $categoryUrlPathGenerator
     * @param PageFactory $resultPageFactory
     * @param ForwardFactory $resultForwardFactory
     * @param Resolver $layerResolver
     * @param CategoryRepositoryInterface $categoryRepository
     * @param ToolbarMemorizer|null $toolbarMemorizer
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        Design $catalogDesign,
        Session $catalogSession,
        Registry $coreRegistry,
        StoreManagerInterface $storeManager,
        CategoryUrlPathGenerator $categoryUrlPathGenerator,
        PageFactory $resultPageFactory,
        ForwardFactory $resultForwardFactory,
        Resolver $layerResolver,
        CategoryRepositoryInterface $categoryRepository,
        ToolbarMemorizer $toolbarMemorizer = null
    ) {
        parent::__construct($context);
        $this->_storeManager = $storeManager;
        $this->_catalogDesign = $catalogDesign;
        $this->_catalogSession = $catalogSession;
        $this->_coreRegistry = $coreRegistry;
        $this->categoryUrlPathGenerator = $categoryUrlPathGenerator;
        $this->resultPageFactory = $resultPageFactory;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->layerResolver = $layerResolver;
        $this->categoryRepository = $categoryRepository;
        $this->toolbarMemorizer = $toolbarMemorizer ?: $context->getObjectManager()->get(ToolbarMemorizer::class);
    }

    /**
     * Initialize requested category object
     *
     * @return Category|bool
     */
    protected function _initCategory()
    {
        $categoryId = (int)$this->getRequest()->getParam('id', false);
        if (!$categoryId) {
            return false;
        }

        try {
            $category = $this->categoryRepository->get($categoryId, $this->_storeManager->getStore()->getId());
        } catch (NoSuchEntityException $e) {
            return false;
        }
        if (!$this->_objectManager->get(\Magento\Catalog\Helper\Category::class)->canShow($category)) {
            return false;
        }
        $this->_catalogSession->setLastVisitedCategoryId($category->getId());
        $this->_coreRegistry->register('current_category', $category);
        $this->toolbarMemorizer->memorizeParams();
        try {
            $this->_eventManager->dispatch(
                'catalog_controller_category_init_after',
                ['category' => $category, 'controller_action' => $this]
            );
        } catch (LocalizedException $e) {
            $this->_objectManager->get(LoggerInterface::class)->critical($e);
            return false;
        }

        return $category;
    }

    /**
     * Category view action
     *
     * @return ResultInterface
     * @throws NoSuchEntityException
     */
    public function execute()
    {
        if ($this->_request->getParam(ActionInterface::PARAM_NAME_URL_ENCODED)) {
            return $this->resultRedirectFactory->create()->setUrl($this->_redirect->getRedirectUrl());
        }
        $category = $this->_initCategory();
        if ($category) {
            $this->layerResolver->create(Resolver::CATALOG_LAYER_CATEGORY);
            $settings = $this->_catalogDesign->getDesignSettings($category);

            // apply custom design
            if ($settings->getCustomDesign()) {
                $this->_catalogDesign->applyCustomDesign($settings->getCustomDesign());
            }

            $this->_catalogSession->setLastViewedCategoryId($category->getId());

            $page = $this->resultPageFactory->create();
            // apply custom layout (page) template once the blocks are generated
            if ($settings->getPageLayout()) {
                $page->getConfig()->setPageLayout($settings->getPageLayout());
            }

            $pageType = $this->getPageType($category);

            if (!$category->hasChildren()) {
                // Two levels removed from parent.  Need to add default page type.
                $parentPageType = strtok($pageType, '_');
                $page->addPageLayoutHandles(['type' => $parentPageType], null, false);
            }
            $page->addPageLayoutHandles(['type' => $pageType], null, false);
            $page->addPageLayoutHandles(['id' => $category->getId()]);

            // apply custom layout update once layout is loaded
            $this->applyLayoutUpdates($page, $settings);

            $page->getConfig()->addBodyClass('page-products')
                ->addBodyClass('categorypath-' . $this->categoryUrlPathGenerator->getUrlPath($category))
                ->addBodyClass('category-' . $category->getUrlKey());

            return $page;
        } elseif (!$this->getResponse()->isRedirect()) {
            return $this->resultForwardFactory->create()->forward('noroute');
        }
    }

    /**
     * Get page type based on category
     *
     * @param Category $category
     * @return string
     */
    private function getPageType(Category $category) : string
    {
        $hasChildren = $category->hasChildren();
        if ($category->getIsAnchor()) {
            return  $hasChildren ? 'layered' : 'layered_without_children';
        }

        return $hasChildren ? 'default' : 'default_without_children';
    }

    /**
     * Apply custom layout updates
     *
     * @param Page $page
     * @param DataObject $settings
     * @return void
     */
    private function applyLayoutUpdates(
        Page $page,
        DataObject $settings
    ) {
        $layoutUpdates = $settings->getLayoutUpdates();
        if ($layoutUpdates && is_array($layoutUpdates)) {
            foreach ($layoutUpdates as $layoutUpdate) {
                $page->addUpdate($layoutUpdate);
                $page->addPageLayoutHandles(['layout_update' => sha1($layoutUpdate)], null, false);
            }
        }
    }
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Controller\Product;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Design;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Controller\Result\Forward;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Json\Helper\Data;
use Magento\Framework\View\Result\PageFactory;
use Magento\Catalog\Controller\Product as ProductAction;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * View a product on storefront. Needs to be accessible by POST because of the store switching
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class View extends ProductAction implements HttpGetActionInterface, HttpPostActionInterface
{
    /**
     * @var \Magento\Catalog\Helper\Product\View
     */
    protected $viewHelper;

    /**
     * @var ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Data
     */
    private $jsonHelper;

    /**
     * @var Design
     */
    private $catalogDesign;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * Constructor
     *
     * @param Context $context
     * @param \Magento\Catalog\Helper\Product\View $viewHelper
     * @param ForwardFactory $resultForwardFactory
     * @param PageFactory $resultPageFactory
     * @param LoggerInterface|null $logger
     * @param Data|null $jsonHelper
     * @param Design|null $catalogDesign
     * @param ProductRepositoryInterface|null $productRepository
     * @param CategoryRepositoryInterface|null $categoryRepository
     * @param StoreManagerInterface|null $storeManager
     */
    public function __construct(
        Context $context,
        \Magento\Catalog\Helper\Product\View $viewHelper,
        ForwardFactory $resultForwardFactory,
        PageFactory $resultPageFactory,
        ?LoggerInterface $logger = null,
        ?Data $jsonHelper = null,
        ?Design $catalogDesign = null,
        ?ProductRepositoryInterface $productRepository = null,
        ?CategoryRepositoryInterface $categoryRepository = null,
        ?StoreManagerInterface $storeManager = null
    ) {
        parent::__construct($context);
        $this->viewHelper = $viewHelper;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->logger = $logger ?: ObjectManager::getInstance()
            ->get(LoggerInterface::class);
        $this->jsonHelper = $jsonHelper ?: ObjectManager::getInstance()
            ->get(Data::class);
        $this->catalogDesign = $catalogDesign ?: ObjectManager::getInstance()
            ->get(Design::class);
        $this->productRepository = $productRepository ?: ObjectManager::getInstance()
            ->get(ProductRepositoryInterface::class);
        $this->categoryRepository = $categoryRepository ?: ObjectManager::getInstance()
            ->get(CategoryRepositoryInterface::class);
        $this->storeManager = $storeManager ?: ObjectManager::getInstance()
            ->get(StoreManagerInterface::class);
    }

    /**
     * Redirect if product failed to load
     *
     * @return Redirect|Forward
     */
    protected function noProductRedirect()
    {
        $store = $this->getRequest()->getQuery('store');
        if (isset($store) && !$this->getResponse()->isRedirect()) {
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('');
        } elseif (!$this->getResponse()->isRedirect()) {
            $resultForward = $this->resultForwardFactory->create();
            $resultForward->forward('noroute');
            return $resultForward;
        }
    }

    /**
     * Product view action
     *
     * @return Forward|Redirect
     */
    public function execute()
    {
        // Get initial data from request
        $categoryId = (int) $this->getRequest()->getParam('category', false);
        $productId = (int) $this->getRequest()->getParam('id');
        $specifyOptions = $this->getRequest()->getParam('options');

        if ($this->getRequest()->isPost() && $this->getRequest()->getParam(self::PARAM_NAME_URL_ENCODED)) {
            $product = $this->_initProduct();

            if (!$product) {
                return $this->noProductRedirect();
            }

            if ($specifyOptions) {
                $notice = $product->getTypeInstance()->getSpecifyOptionMessage();
                $this->messageManager->addNoticeMessage($notice);
            }

            if ($this->getRequest()->isAjax()) {
                $this->getResponse()->representJson(
                    $this->jsonHelper->jsonEncode(
                        [
                            'backUrl' => $this->_redirect->getRedirectUrl()
                        ]
                    )
                );
                return;
            }
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setRefererOrBaseUrl();
            return $resultRedirect;
        }

        // Prepare helper and params
        $params = new DataObject();
        $params->setCategoryId($categoryId);
        $params->setSpecifyOptions($specifyOptions);

        // Render page
        try {
            $this->applyCustomDesign($productId);
            $page = $this->resultPageFactory->create();
            $this->viewHelper->prepareAndRender($page, $productId, $this, $params);
            return $page;
        } catch (NoSuchEntityException $e) {
            return $this->noProductRedirect();
        } catch (\Exception $e) {
            $this->logger->critical($e);
            $resultForward = $this->resultForwardFactory->create();
            $resultForward->forward('noroute');
            return $resultForward;
        }
    }

    /**
     * Apply custom design from product design settings
     *
     * @param int $productId
     * @throws NoSuchEntityException
     */
    private function applyCustomDesign(int $productId): void
    {
        $product = $this->productRepository->getById($productId, false, $this->storeManager->getStore()->getId());
        $productSettings = $this->catalogDesign->getDesignSettings($product);
        if ($productSettings->getCustomDesign()) {
            $this->catalogDesign->applyCustomDesign($productSettings->getCustomDesign());
        } else {
            foreach ($product->getCategoryIds() as $categoryId) {
                $category = $this->categoryRepository->get($categoryId, $this->storeManager->getStore()->getId());
                $categorySettings = $this->catalogDesign->getDesignSettings($category);
                if ($categorySettings->getCustomDesign()) {
                    $this->catalogDesign->applyCustomDesign($categorySettings->getCustomDesign());
                }
            }
        }
    }
}

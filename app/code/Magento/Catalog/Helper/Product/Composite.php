<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Helper\Product;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\View\Result\LayoutFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Catalog\Helper\Product;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Controller\RegistryConstants;
use Magento\Framework\Registry;

/**
 * Adminhtml catalog product composite helper
 *
 * @api
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 2.0.0
 */
class Composite extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Core registry
     *
     * @var Registry
     * @since 2.0.0
     */
    protected $_coreRegistry = null;

    /**
     * Catalog product
     *
     * @var Product
     * @since 2.0.0
     */
    protected $_catalogProduct = null;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     * @since 2.0.0
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\View\Result\LayoutFactory
     * @since 2.0.0
     */
    protected $resultLayoutFactory;

    /**
     * @var ProductRepositoryInterface
     * @since 2.0.0
     */
    protected $productRepository;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param Product $catalogProduct
     * @param Registry $coreRegistry
     * @param LayoutFactory $resultLayoutFactory
     * @param ProductRepositoryInterface $productRepository
     * @since 2.0.0
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        Product $catalogProduct,
        Registry $coreRegistry,
        LayoutFactory $resultLayoutFactory,
        ProductRepositoryInterface $productRepository
    ) {
        $this->_storeManager = $storeManager;
        $this->_coreRegistry = $coreRegistry;
        $this->_catalogProduct = $catalogProduct;
        $this->resultLayoutFactory = $resultLayoutFactory;
        $this->productRepository = $productRepository;
        parent::__construct($context);
    }

    /**
     * Init layout of product configuration update result
     *
     * @return \Magento\Framework\View\Result\Layout
     * @since 2.0.0
     */
    protected function _initUpdateResultLayout()
    {
        $resultLayout = $this->resultLayoutFactory->create();
        $resultLayout->addHandle('CATALOG_PRODUCT_COMPOSITE_UPDATE_RESULT');
        return $resultLayout;
    }

    /**
     * Prepares and render result of composite product configuration update for a case
     * when single configuration submitted
     *
     * @param \Magento\Framework\DataObject $updateResult
     * @return \Magento\Framework\View\Result\Layout
     * @since 2.0.0
     */
    public function renderUpdateResult(\Magento\Framework\DataObject $updateResult)
    {
        $this->_coreRegistry->register('composite_update_result', $updateResult);
        return $this->_initUpdateResultLayout();
    }

    /**
     * Init composite product configuration layout
     *
     * $isOk - true or false, whether action was completed nicely or with some error
     * If $isOk is FALSE (some error during configuration), so $productType must be null
     *
     * @param bool $isOk
     * @param string $productType
     * @return \Magento\Framework\View\Result\Layout
     * @since 2.0.0
     */
    protected function _initConfigureResultLayout($isOk, $productType)
    {
        $resultLayout = $this->resultLayoutFactory->create();
        if ($isOk) {
            $resultLayout->addHandle('CATALOG_PRODUCT_COMPOSITE_CONFIGURE')
                ->addHandle('catalog_product_view_type_' . $productType);
        } else {
            $resultLayout->addHandle('CATALOG_PRODUCT_COMPOSITE_CONFIGURE_ERROR');
        }
        return $resultLayout;
    }

    /**
     * Prepares and render result of composite product configuration request
     *
     * The $configureResult variable holds either:
     *  - 'ok' = true, and 'product_id', 'buy_request', 'current_store_id', 'current_customer_id'
     *  - 'error' = true, and 'message' to show
     *
     * @param \Magento\Framework\DataObject $configureResult
     * @return \Magento\Framework\View\Result\Layout
     * @since 2.0.0
     */
    public function renderConfigureResult(\Magento\Framework\DataObject $configureResult)
    {
        try {
            if (!$configureResult->getOk()) {
                throw new \Magento\Framework\Exception\LocalizedException(__($configureResult->getMessage()));
            }

            $currentStoreId = (int)$configureResult->getCurrentStoreId();
            if (!$currentStoreId) {
                $currentStoreId = $this->_storeManager->getStore()->getId();
            }

            $product = $this->productRepository->getById($configureResult->getProductId(), false, $currentStoreId);

            $this->_coreRegistry->register('current_product', $product);
            $this->_coreRegistry->register('product', $product);

            // Register customer we're working with
            $customerId = (int)$configureResult->getCurrentCustomerId();
            $this->_coreRegistry->register(RegistryConstants::CURRENT_CUSTOMER_ID, $customerId);

            // Prepare buy request values
            $buyRequest = $configureResult->getBuyRequest();
            if ($buyRequest) {
                $this->_catalogProduct->prepareProductOptions($product, $buyRequest);
            }

            $isOk = true;
            $productType = $product->getTypeId();
        } catch (\Exception $e) {
            $isOk = false;
            $productType = null;
            $this->_coreRegistry->register('composite_configure_result_error_message', $e->getMessage());
        }

        return $this->_initConfigureResultLayout($isOk, $productType);
    }
}

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
namespace Magento\Catalog\Helper\Product;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ViewInterface;
use Magento\Catalog\Helper\Product;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\StoreManagerInterface;
use Magento\Customer\Controller\RegistryConstants;
use Magento\Customer\Model\Converter;
use Magento\Framework\Registry;

/**
 * Adminhtml catalog product composite helper
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Composite extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Core registry
     *
     * @var Registry
     */
    protected $_coreRegistry = null;

    /**
     * Catalog product
     *
     * @var Product
     */
    protected $_catalogProduct = null;

    /**
     * @var \Magento\Framework\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var ProductFactory
     */
    protected $_productFactory;

    /**
     * @var ViewInterface
     */
    protected $_view;

    /**
     * @var Converter
     */
    protected $_converter;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param ProductFactory $productFactory
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     * @param Product $catalogProduct
     * @param Registry $coreRegistry
     * @param ViewInterface $view
     * @param Converter $converter
     */
    public function __construct(
        Context $context,
        ProductFactory $productFactory,
        StoreManagerInterface $storeManager,
        Product $catalogProduct,
        Registry $coreRegistry,
        ViewInterface $view,
        Converter $converter
    ) {
        $this->_productFactory = $productFactory;
        $this->_storeManager = $storeManager;
        $this->_coreRegistry = $coreRegistry;
        $this->_catalogProduct = $catalogProduct;
        $this->_view = $view;
        $this->_converter = $converter;
        parent::__construct($context);
    }

    /**
     * Init layout of product configuration update result
     *
     * @return $this
     */
    protected function _initUpdateResultLayout()
    {
        $this->_view->getLayout()->getUpdate()->addHandle('CATALOG_PRODUCT_COMPOSITE_UPDATE_RESULT');
        $this->_view->loadLayoutUpdates();
        $this->_view->generateLayoutXml();
        $this->_view->generateLayoutBlocks();
        return $this;
    }

    /**
     * Prepares and render result of composite product configuration update for a case
     * when single configuration submitted
     *
     * @param \Magento\Framework\Object $updateResult
     * @return void
     */
    public function renderUpdateResult(\Magento\Framework\Object $updateResult)
    {
        $this->_coreRegistry->register('composite_update_result', $updateResult);

        $this->_initUpdateResultLayout();
        $this->_view->renderLayout();
    }

    /**
     * Init composite product configuration layout
     *
     * $isOk - true or false, whether action was completed nicely or with some error
     * If $isOk is FALSE (some error during configuration), so $productType must be null
     *
     * @param bool $isOk
     * @param string $productType
     * @return $this
     */
    protected function _initConfigureResultLayout($isOk, $productType)
    {
        $update = $this->_view->getLayout()->getUpdate();
        if ($isOk) {
            $update->addHandle(
                'CATALOG_PRODUCT_COMPOSITE_CONFIGURE'
            )->addHandle(
                'catalog_product_view_type_' . $productType
            );
        } else {
            $update->addHandle('CATALOG_PRODUCT_COMPOSITE_CONFIGURE_ERROR');
        }
        $this->_view->loadLayoutUpdates();
        $this->_view->generateLayoutXml();
        $this->_view->generateLayoutBlocks();
        return $this;
    }

    /**
     * Prepares and render result of composite product configuration request
     *
     * The $configureResult variable holds either:
     *  - 'ok' = true, and 'product_id', 'buy_request', 'current_store_id', 'current_customer_id'
     *  - 'error' = true, and 'message' to show
     *
     * @param \Magento\Framework\Object $configureResult
     * @return void
     */
    public function renderConfigureResult(\Magento\Framework\Object $configureResult)
    {
        try {
            if (!$configureResult->getOk()) {
                throw new \Magento\Framework\Model\Exception($configureResult->getMessage());
            }

            $currentStoreId = (int)$configureResult->getCurrentStoreId();
            if (!$currentStoreId) {
                $currentStoreId = $this->_storeManager->getStore()->getId();
            }

            $product = $this->_productFactory->create()->setStoreId(
                $currentStoreId
            )->load(
                $configureResult->getProductId()
            );
            if (!$product->getId()) {
                throw new \Magento\Framework\Model\Exception(__('The product is not loaded.'));
            }
            $this->_coreRegistry->register('current_product', $product);
            $this->_coreRegistry->register('product', $product);

            // Register customer we're working with
            $customerId = (int)$configureResult->getCurrentCustomerId();
            // TODO: Remove the customer model from the registry once all readers are refactored
            $customerModel = $this->_converter->loadCustomerModel($customerId);
            $this->_coreRegistry->register(RegistryConstants::CURRENT_CUSTOMER, $customerModel);
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

        $this->_initConfigureResultLayout($isOk, $productType);
        $this->_view->renderLayout();
    }
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Controller\Adminhtml\Product;

use Magento\Backend\App\Action\Context;
use Magento\Catalog\Model\Product\Validator;
use Magento\Catalog\Model\ProductFactory;
use Magento\Eav\Model\Entity\Attribute\Exception;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Catalog\Controller\Adminhtml\Product;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\DateTime\Filter\Date;
use Magento\Framework\View\LayoutFactory;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Product validate
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Validate extends Product implements HttpPostActionInterface, HttpGetActionInterface
{
    /**
     * @var Date
     *
     * @deprecated 101.0.0
     */
    protected $_dateFilter;

    /**
     * @var Validator
     */
    protected $productValidator;

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var LayoutFactory
     */
    protected $layoutFactory;

    /**
     * @var ProductFactory
     */
    protected $productFactory;

    /**
     * @var Initialization\Helper
     */
    protected $initializationHelper;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param Context $context
     * @param Builder $productBuilder
     * @param Date $dateFilter
     * @param Validator $productValidator
     * @param JsonFactory $resultJsonFactory
     * @param LayoutFactory $layoutFactory
     * @param ProductFactory $productFactory
     * @param StoreManagerInterface $storeManager
     * @param Initialization\Helper $initializationHelper
     */
    public function __construct(
        Context $context,
        Product\Builder $productBuilder,
        Date $dateFilter,
        Validator $productValidator,
        JsonFactory $resultJsonFactory,
        LayoutFactory $layoutFactory,
        ProductFactory $productFactory,
        StoreManagerInterface $storeManager,
        Initialization\Helper $initializationHelper
    ) {
        $this->_dateFilter = $dateFilter;
        $this->productValidator = $productValidator;
        parent::__construct($context, $productBuilder);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->layoutFactory = $layoutFactory;
        $this->productFactory = $productFactory;
        $this->storeManager = $storeManager;
        $this->initializationHelper = $initializationHelper;
    }

    /**
     * Validate product
     *
     * @return Json
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        $response = new DataObject();
        $response->setError(false);

        try {
            $productData = $this->getRequest()->getPost('product', []);

            if ($productData && !isset($productData['stock_data']['use_config_manage_stock'])) {
                $productData['stock_data']['use_config_manage_stock'] = 0;
            }
            $storeId = $this->getRequest()->getParam('store', 0);
            $store = $this->storeManager->getStore($storeId);
            $this->storeManager->setCurrentStore($store->getCode());
            /* @var $product \Magento\Catalog\Model\Product */
            $product = $this->productFactory->create();
            $product->setData('_edit_mode', true);
            if ($storeId) {
                $product->setStoreId($storeId);
            }
            $setId = $this->getRequest()->getPost('set') ?: $this->getRequest()->getParam('set');
            if ($setId) {
                $product->setAttributeSetId($setId);
            }
            $typeId = $this->getRequest()->getParam('type');
            if ($typeId) {
                $product->setTypeId($typeId);
            }
            $productId = $this->getRequest()->getParam('id');
            if ($productId) {
                $product->load($productId);
            }
            $product = $this->initializationHelper->initializeFromData($product, $productData);

            /* set restrictions for date ranges */
            $resource = $product->getResource();
            $resource->getAttribute('special_from_date')->setMaxValue($product->getSpecialToDate());
            $resource->getAttribute('news_from_date')->setMaxValue($product->getNewsToDate());
            $resource->getAttribute('custom_design_from')->setMaxValue($product->getCustomDesignTo());

            $this->productValidator->validate($product, $this->getRequest(), $response);
        } catch (Exception $e) {
            $response->setError(true);
            $response->setAttribute($e->getAttributeCode());
            $response->setMessages([$e->getMessage()]);
        } catch (LocalizedException $e) {
            $response->setError(true);
            $response->setMessages([$e->getMessage()]);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $layout = $this->layoutFactory->create();
            $layout->initMessages();
            $response->setError(true);
            $response->setHtmlMessage($layout->getMessagesBlock()->getGroupedHtml());
        }

        return $this->resultJsonFactory->create()->setData($response);
    }
}

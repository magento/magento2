<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Controller\Adminhtml\Product;

use Magento\Backend\App\Action;
use Magento\Catalog\Controller\Adminhtml\Product;
use Magento\Framework\App\ObjectManager;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Product validate
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Validate extends \Magento\Catalog\Controller\Adminhtml\Product
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\Filter\Date
     *
     * @deprecated
     */
    protected $_dateFilter;

    /**
     * @var \Magento\Catalog\Model\Product\Validator
     */
    protected $productValidator;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
    protected $layoutFactory;

    /** @var \Magento\Catalog\Model\ProductFactory */
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
     * @param Action\Context $context
     * @param Builder $productBuilder
     * @param \Magento\Framework\Stdlib\DateTime\Filter\Date $dateFilter
     * @param \Magento\Catalog\Model\Product\Validator $productValidator
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        Product\Builder $productBuilder,
        \Magento\Framework\Stdlib\DateTime\Filter\Date $dateFilter,
        \Magento\Catalog\Model\Product\Validator $productValidator,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory
    ) {
        $this->_dateFilter = $dateFilter;
        $this->productValidator = $productValidator;
        parent::__construct($context, $productBuilder);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->layoutFactory = $layoutFactory;
        $this->productFactory = $productFactory;
    }

    /**
     * Validate product
     *
     * @return \Magento\Framework\Controller\Result\Json
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        $response = new \Magento\Framework\DataObject();
        $response->setError(false);

        try {
            $productData = $this->getRequest()->getPost('product', []);

            if ($productData && !isset($productData['stock_data']['use_config_manage_stock'])) {
                $productData['stock_data']['use_config_manage_stock'] = 0;
            }
            $storeId = $this->getRequest()->getParam('store', 0);
            $store = $this->getStoreManager()->getStore($storeId);
            $this->getStoreManager()->setCurrentStore($store->getCode());
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
            $product = $this->getInitializationHelper()->initializeFromData($product, $productData);

            /* set restrictions for date ranges */
            $resource = $product->getResource();
            $resource->getAttribute('special_from_date')->setMaxValue($product->getSpecialToDate());
            $resource->getAttribute('news_from_date')->setMaxValue($product->getNewsToDate());
            $resource->getAttribute('custom_design_from')->setMaxValue($product->getCustomDesignTo());

            $this->productValidator->validate($product, $this->getRequest(), $response);
        } catch (\Magento\Eav\Model\Entity\Attribute\Exception $e) {
            $response->setError(true);
            $response->setAttribute($e->getAttributeCode());
            $response->setMessages([$e->getMessage()]);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $response->setError(true);
            $response->setMessages([$e->getMessage()]);
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
            $layout = $this->layoutFactory->create();
            $layout->initMessages();
            $response->setError(true);
            $response->setHtmlMessage($layout->getMessagesBlock()->getGroupedHtml());
        }

        return $this->resultJsonFactory->create()->setData($response);
    }

    /**
     * @return StoreManagerInterface
     * @deprecated
     */
    private function getStoreManager()
    {
        if (null === $this->storeManager) {
            $this->storeManager = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Store\Model\StoreManagerInterface::class);
        }
        return $this->storeManager;
    }

    /**
     * @return Initialization\Helper
     * @deprecated
     */
    protected function getInitializationHelper()
    {
        if (null === $this->initializationHelper) {
            $this->initializationHelper = ObjectManager::getInstance()->get(Initialization\Helper::class);
        }
        return $this->initializationHelper;
    }
}

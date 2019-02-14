<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Controller\Adminhtml\Product\Action\Attribute;

use Magento\Catalog\Api\Data\MassActionInterfaceFactory;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Backend\App\Action;
use Magento\Framework\MessageQueue\PublisherInterface;
use Magento\Framework\App\ObjectManager;

/**
 * Class Save
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Save extends \Magento\Catalog\Controller\Adminhtml\Product\Action\Attribute implements HttpPostActionInterface
{
    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Flat\Processor
     */
    protected $_productFlatIndexerProcessor;

    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Price\Processor
     */
    protected $_productPriceIndexerProcessor;

    /**
     * Catalog product
     *
     * @var \Magento\Catalog\Helper\Product
     */
    protected $_catalogProduct;

    /**
     * @var \Magento\CatalogInventory\Api\Data\StockItemInterfaceFactory
     */
    protected $stockItemFactory;

    /**
     * Stock Indexer
     *
     * @var \Magento\CatalogInventory\Model\Indexer\Stock\Processor
     */
    protected $_stockIndexerProcessor;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var PublisherInterface
     */
    private $messagePublisher;
    /**
     * @var MassActionInterfaceFactory|null
     */
    private $massActionFactory;


    /**
     * @param Action\Context $context
     * @param \Magento\Catalog\Helper\Product\Edit\Action\Attribute $attributeHelper
     * @param \Magento\Catalog\Model\Indexer\Product\Flat\Processor $productFlatIndexerProcessor
     * @param \Magento\Catalog\Model\Indexer\Product\Price\Processor $productPriceIndexerProcessor
     * @param \Magento\CatalogInventory\Model\Indexer\Stock\Processor $stockIndexerProcessor
     * @param \Magento\Catalog\Helper\Product $catalogProduct
     * @param \Magento\CatalogInventory\Api\Data\StockItemInterfaceFactory $stockItemFactory
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @param PublisherInterface|null $publisher
     * @param MassActionInterfaceFactory|null $massAction
     */
    public function __construct(
        Action\Context $context,
        \Magento\Catalog\Helper\Product\Edit\Action\Attribute $attributeHelper,
        \Magento\Catalog\Model\Indexer\Product\Flat\Processor $productFlatIndexerProcessor,
        \Magento\Catalog\Model\Indexer\Product\Price\Processor $productPriceIndexerProcessor,
        \Magento\CatalogInventory\Model\Indexer\Stock\Processor $stockIndexerProcessor,
        \Magento\Catalog\Helper\Product $catalogProduct,
        \Magento\CatalogInventory\Api\Data\StockItemInterfaceFactory $stockItemFactory,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        PublisherInterface $publisher = null,
        MassActionInterfaceFactory $massAction = null
    ) {
        $this->_productFlatIndexerProcessor = $productFlatIndexerProcessor;
        $this->_productPriceIndexerProcessor = $productPriceIndexerProcessor;
        $this->_stockIndexerProcessor = $stockIndexerProcessor;
        $this->_catalogProduct = $catalogProduct;
        $this->stockItemFactory = $stockItemFactory;
        parent::__construct($context, $attributeHelper);
        $this->dataObjectHelper = $dataObjectHelper;
        $this->messagePublisher = $publisher ?: ObjectManager::getInstance()->get(PublisherInterface::class);
        $this->massActionFactory = $massAction ?: ObjectManager::getInstance()->get(MassActionInterfaceFactory::class);
    }

    /**
     * Update product attributes
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function execute()
    {
        if (!$this->_validateProducts()) {
            return $this->resultRedirectFactory->create()->setPath('catalog/product/', ['_current' => true]);
        }

        /* Collect Data */
        $inventoryData = $this->getRequest()->getParam('inventory', []);
        $attributesData = $this->getRequest()->getParam('attributes', []);
        $websiteRemoveData = $this->getRequest()->getParam('remove_website_ids', []);
        $websiteAddData = $this->getRequest()->getParam('add_website_ids', []);

        /* Prepare inventory data item options (use config settings) */
        $options = $this->_objectManager->get(StockConfigurationInterface::class)->getConfigItemOptions();
        foreach ($options as $option) {
            if (isset($inventoryData[$option]) && !isset($inventoryData['use_config_' . $option])) {
                $inventoryData['use_config_' . $option] = 0;
            }
        }

        $massAction = $this->massActionFactory->create();
        $massAction->setInventory($inventoryData);
        $massAction->setAttributes($attributesData);
        $massAction->setWebsiteRemove($websiteRemoveData);
        $massAction->setWebsiteAdd($websiteAddData);
        $massAction->setStoreId($this->attributeHelper->getSelectedStoreId());
        $massAction->setProductIds($this->attributeHelper->getProductIds());
        $massAction->setWebsiteId($this->attributeHelper->getStoreWebsiteId($massAction->getStoreId()));

        try {
            $this->messagePublisher->publish('product_action_attribute.update', $massAction);
            $this->messageManager->addSuccessMessage(__('Message is added to queue, wait'));
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage(
                $e,
                __('Something went wrong while updating the product(s) attributes.')
            );
        }

        return $this->resultRedirectFactory->create()
            ->setPath('catalog/product/', ['store' => $massAction->getStoreId()]);
    }
}

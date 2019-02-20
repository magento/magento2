<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Controller\Adminhtml\Product\Action\Attribute;

use Magento\Catalog\Api\Data\MassActionInterfaceFactory;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Backend\App\Action;
use Magento\Framework\MessageQueue\PublisherInterface;
use Magento\Framework\App\ObjectManager;

/**
 * Class Save
 */
class Save extends \Magento\Catalog\Controller\Adminhtml\Product\Action\Attribute implements HttpPostActionInterface
{
    /**
     * @var PublisherInterface
     */
    private $messagePublisher;

    /**
     * @var MassActionInterfaceFactory|null
     */
    private $massActionFactory;

    /**
     * @var StockConfigurationInterface
     */
    private $stockConfiguration;

    /**
     * @param Action\Context $context
     * @param \Magento\Catalog\Helper\Product\Edit\Action\Attribute $attributeHelper
     * @param StockConfigurationInterface|null $stockConfiguration
     * @param PublisherInterface|null $publisher
     * @param MassActionInterfaceFactory|null $massAction
     */
    public function __construct(
        Action\Context $context,
        \Magento\Catalog\Helper\Product\Edit\Action\Attribute $attributeHelper,
        StockConfigurationInterface $stockConfiguration = null,
        PublisherInterface $publisher = null,
        MassActionInterfaceFactory $massAction = null
    ) {
        parent::__construct($context, $attributeHelper);
        $this->messagePublisher = $publisher ?: ObjectManager::getInstance()->get(PublisherInterface::class);
        $this->massActionFactory = $massAction ?: ObjectManager::getInstance()->get(MassActionInterfaceFactory::class);
        $this->stockConfiguration = $stockConfiguration
            ?: ObjectManager::getInstance()->get(StockConfigurationInterface::class);
    }

    /**
     * Update product attributes
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        if (!$this->_validateProducts()) {
            return $this->resultRedirectFactory->create()->setPath('catalog/product/', ['_current' => true]);
        }

        /* Collect Data */
        $inventoryData = $this->getRequest()->getParam('inventory', []);
        $inventoryData = $this->addConfigSettings($inventoryData);
        $attributesData = $this->getRequest()->getParam('attributes', []);
        $websiteRemoveData = $this->getRequest()->getParam('remove_website_ids', []);
        $websiteAddData = $this->getRequest()->getParam('add_website_ids', []);
        $storeId = $this->attributeHelper->getSelectedStoreId();
        $productIds = $this->attributeHelper->getProductIds();
        $websiteId = $this->attributeHelper->getStoreWebsiteId($storeId);

        /* Create DTO for queue */
        $massAction = $this->massActionFactory->create();
        $massAction->setInventory($inventoryData);
        $massAction->setAttributeValues(array_values($attributesData));
        $massAction->setAttributeKeys(array_keys($attributesData));
        $massAction->setWebsiteRemove($websiteRemoveData);
        $massAction->setWebsiteAdd($websiteAddData);
        $massAction->setStoreId($storeId);
        $massAction->setProductIds($productIds);
        $massAction->setWebsiteId($websiteId);

        try {
            $this->messagePublisher->publish('product_action_attribute.update', $massAction);
            $this->messageManager->addSuccessMessage(__('Message is added to queue'));
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage(
                $e,
                __('Something went wrong while updating the product(s) attributes.')
            );
        }

        return $this->resultRedirectFactory->create()->setPath('catalog/product/', ['store' => $storeId]);
    }

    /**
     * Prepare inventory data item options (use config settings)
     * @param $inventoryData
     * @return mixed
     */
    private function addConfigSettings($inventoryData)
    {
        $options = $this->stockConfiguration->getConfigItemOptions();
        foreach ($options as $option) {
            $useConfig = 'use_config_' . $option;
            if (isset($inventoryData[$option]) && !isset($inventoryData[$useConfig])) {
                $inventoryData[$useConfig] = 0;
            }
        }
        return $inventoryData;
    }
}

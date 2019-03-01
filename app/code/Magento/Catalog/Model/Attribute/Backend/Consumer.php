<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\Attribute\Backend;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\TemporaryStateExceptionInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Bulk\OperationInterface;

/**
 * Consumer for export message.
 */
class Consumer
{
    /**
     * @var \Magento\Framework\Notification\NotifierInterface
     */
    private $notifier;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Flat\Processor
     */
    private $productFlatIndexerProcessor;

    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Price\Processor
     */
    private $productPriceIndexerProcessor;

    /**
     * @var \Magento\Catalog\Helper\Product
     */
    private $catalogProduct;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $eventManager;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var \Magento\Catalog\Model\Product\Action
     */
    private $productAction;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    private $serializer;

    /**
     * @var \Magento\Framework\Bulk\OperationManagementInterface
     */
    private $operationManagement;

    /**
     * @param \Magento\Catalog\Helper\Product $catalogProduct
     * @param \Magento\Catalog\Model\Indexer\Product\Flat\Processor $productFlatIndexerProcessor
     * @param \Magento\Catalog\Model\Indexer\Product\Price\Processor $productPriceIndexerProcessor
     * @param \Magento\Framework\Bulk\OperationManagementInterface $operationManagement
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Catalog\Model\Product\Action $action
     * @param \Magento\Framework\Notification\NotifierInterface $notifier
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Serialize\SerializerInterface $serializer
     */
    public function __construct(
        \Magento\Catalog\Helper\Product $catalogProduct,
        \Magento\Catalog\Model\Indexer\Product\Flat\Processor $productFlatIndexerProcessor,
        \Magento\Catalog\Model\Indexer\Product\Price\Processor $productPriceIndexerProcessor,
        \Magento\Framework\Bulk\OperationManagementInterface $operationManagement,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Catalog\Model\Product\Action $action,
        \Magento\Framework\Notification\NotifierInterface $notifier,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Serialize\SerializerInterface $serializer
    ) {
        $this->catalogProduct = $catalogProduct;
        $this->productFlatIndexerProcessor = $productFlatIndexerProcessor;
        $this->productPriceIndexerProcessor = $productPriceIndexerProcessor;
        $this->notifier = $notifier;
        $this->eventManager = $eventManager;
        $this->objectManager = ObjectManager::getInstance();
        $this->productAction = $action;
        $this->logger = $logger;
        $this->serializer = $serializer;
        $this->operationManagement = $operationManagement;
    }

    /**
     * Processing batch of operations for update tier prices.
     *
     * @param \Magento\AsynchronousOperations\Api\Data\OperationListInterface $operationList
     * @return void
     * @throws \InvalidArgumentException
     */
    public function process(\Magento\AsynchronousOperations\Api\Data\OperationListInterface $operationList)
    {
        try {
            foreach ($operationList->getItems() as  $operation) {
                $serializedData = $operation->getSerializedData();
                $data = $this->serializer->unserialize($serializedData);

                $this->execute($data);
            }
        } catch (NoSuchEntityException $e) {
            $this->logger->critical($e->getMessage());
            $status = ($e instanceof TemporaryStateExceptionInterface)
                ? OperationInterface::STATUS_TYPE_RETRIABLY_FAILED
                : OperationInterface::STATUS_TYPE_NOT_RETRIABLY_FAILED;
            $errorCode = $e->getCode();
            $message = $e->getMessage();
        } catch (LocalizedException $e) {
            $this->logger->critical($e->getMessage());
            $status = OperationInterface::STATUS_TYPE_NOT_RETRIABLY_FAILED;
            $errorCode = $e->getCode();
            $message = $e->getMessage();
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
            $status = OperationInterface::STATUS_TYPE_NOT_RETRIABLY_FAILED;
            $errorCode = $e->getCode();
            $message = __('Sorry, something went wrong during product prices update. Please see log for details.');
        }

        //update operation status based on result performing operation(it was successfully executed or exception occurs
        $this->operationManagement->changeOperationStatus(
            $operation->getId(),
            $status,
            $errorCode,
            $message,
            $serializedData
        );
    }

    /**
     * @param $productIds
     * @param $storeId
     * @param $attributesData
     * @return mixed
     */
    private function getAttributesData($productIds, $storeId, $attributesData)
    {
        $dateFormat = $this->objectManager->get(TimezoneInterface::class)->getDateFormat(\IntlDateFormatter::SHORT);
        $config = $this->objectManager->get(\Magento\Eav\Model\Config::class);

        foreach ($attributesData as $attributeCode => $value) {
            $attribute = $config->getAttribute(\Magento\Catalog\Model\Product::ENTITY, $attributeCode);
            if (!$attribute->getAttributeId()) {
                unset($attributesData[$attributeCode]);
                continue;
            }
            if ($attribute->getBackendType() == 'datetime') {
                if (!empty($value)) {
                    $filterInput = new \Zend_Filter_LocalizedToNormalized(['date_format' => $dateFormat]);
                    $filterInternal = new \Zend_Filter_NormalizedToLocalized(
                        ['date_format' => \Magento\Framework\Stdlib\DateTime::DATE_INTERNAL_FORMAT]
                    );
                    $value = $filterInternal->filter($filterInput->filter($value));
                } else {
                    $value = null;
                }
                $attributesData[$attributeCode] = $value;
            } elseif ($attribute->getFrontendInput() == 'multiselect') {
                // Check if 'Change' checkbox has been checked by admin for this attribute
                $isChanged = (bool)$this->getRequest()->getPost('toggle_' . $attributeCode);
                if (!$isChanged) {
                    unset($attributesData[$attributeCode]);
                    continue;
                }
                if (is_array($value)) {
                    $value = implode(',', $value);
                }
                $attributesData[$attributeCode] = $value;
            }
        }

        $this->productAction->updateAttributes($productIds, $attributesData, $storeId);
        return $attributesData;
    }

    /**
     * @param $productIds
     * @param $websiteRemoveData
     * @param $websiteAddData
     */
    private function updateWebsiteInProducts($productIds, $websiteRemoveData, $websiteAddData): void
    {
        if ($websiteRemoveData) {
            $this->productAction->updateWebsites($productIds, $websiteRemoveData, 'remove');
        }
        if ($websiteAddData) {
            $this->productAction->updateWebsites($productIds, $websiteAddData, 'add');
        }

        $this->eventManager->dispatch('catalog_product_to_website_change', ['products' => $productIds]);
    }

    /**
     * @param $productIds
     * @param $attributesData
     * @param $websiteRemoveData
     * @param $websiteAddData
     */
    private function reindex($productIds, $attributesData, $websiteRemoveData, $websiteAddData): void
    {
        if ($this->catalogProduct->isDataForPriceIndexerWasChanged($attributesData)
            || !empty($websiteRemoveData)
            || !empty($websiteAddData)
        ) {
            $this->productPriceIndexerProcessor->reindexList($productIds);
        }
    }

    /**
     * @param $data
     */
    private function execute($data): void
    {
        if ($data['website_assign'] || $data['website_detach']) {
            $this->updateWebsiteInProducts([$data['product_id']], $data['website_detach'], $data['website_assign']);
        }

        if ($data['attributes']) {
            $attributesData = $this->getAttributesData(
                [$data['product_id']],
                $data['store_id'],
                $data['attributes']
            );
            $this->reindex([$data['product_id']], $attributesData, $data['website_detach'], $data['website_assign']);
        }

        $this->productFlatIndexerProcessor->reindexList([$data['product_id']]);

        $this->notifier->addNotice(
            __('Product attributes updated'),
            __('A total of %1 record(s) were updated.', count([$data['product_id']]))
        );
    }
}

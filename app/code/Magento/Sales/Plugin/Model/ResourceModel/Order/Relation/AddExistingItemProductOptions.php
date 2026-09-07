<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Plugin\Model\ResourceModel\Order\Relation;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Type\AbstractType;
use Magento\Framework\DataObject;
use Magento\Framework\DataObject\Factory as DataObjectFactory;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Model\ResourceModel\Order\Item as OrderItemResource;
use Magento\Sales\Model\ResourceModel\Order\Relation;

/**
 * Plugin adds missing options and labels
 */
class AddExistingItemProductOptions
{
    /**
     * @param OrderItemResource $orderItemResource
     * @param Json $serializer
     * @param ProductRepositoryInterface $productRepository
     * @param DataObjectFactory $dataObjectFactory
     */
    public function __construct(
        private readonly OrderItemResource $orderItemResource,
        private readonly Json $serializer,
        private readonly ProductRepositoryInterface $productRepository,
        private readonly DataObjectFactory $dataObjectFactory
    ) {
    }

    /**
     * Convert product options from serialized string to array format.
     *
     * @param string $productOptions
     * @return array
     */
    private function getProductOptionsArray(string $productOptions): array
    {
        try {
            $options = $this->serializer->unserialize($productOptions);
        } catch (\Exception $e) {
            $options = [];
        }
        return $options;
    }

    /**
     * Retrieve existing order item row by item ID.
     *
     * @param int $itemId
     * @return array
     */
    private function getExistingOrderItemProductOptions(int $itemId): array
    {
        $productOptions = [];
        try {
            $row = $this->orderItemResource->getConnection()
                ->fetchRow(
                    $this->orderItemResource->getConnection()->select()
                        ->from($this->orderItemResource->getMainTable())
                        ->where('item_id = ?', $itemId)
                );
            if (isset($row['product_options']) && is_string($row['product_options'])) {
                $productOptions = $this->getProductOptionsArray($row['product_options']);
            }
        } catch (\Exception $e) {
            $productOptions = [];
        }
        return $productOptions;
    }

    /**
     * Return product options generated from the order item's public product option payload.
     *
     * @param OrderItemInterface $item
     * @return array
     */
    private function getNewOrderItemProductOptions(OrderItemInterface $item): array
    {
        $buyRequest = $this->getBuyRequest($item);
        if (!$buyRequest->hasData()) {
            return $item->getProductOptions() ?: [];
        }

        $productOptions = $this->getProductTypeOrderOptions($item, $buyRequest);
        $productOptions = array_replace($productOptions, $item->getProductOptions() ?: []);
        $productOptions['info_buyRequest'] = !empty($productOptions['info_buyRequest'])
            ? array_merge($productOptions['info_buyRequest'], $buyRequest->toArray())
            : $buyRequest->toArray();

        return $productOptions;
    }

    /**
     * Build a buy request from the Web API product_option payload.
     *
     * @param OrderItemInterface $item
     * @return DataObject
     */
    private function getBuyRequest(OrderItemInterface $item): DataObject
    {
        $requestData = $this->getInitialBuyRequestData($item);
        $extensionAttributes = $item->getProductOption()?->getExtensionAttributes();
        if ($extensionAttributes) {
            $requestData = $this->applyExtensionAttributesToBuyRequest($requestData, $extensionAttributes);
        }

        return $this->dataObjectFactory->create($requestData);
    }

    /**
     * Build initial buy request data from persisted product options and quantity.
     *
     * @param OrderItemInterface $item
     * @return array
     */
    private function getInitialBuyRequestData(OrderItemInterface $item): array
    {
        $requestData = [];
        $productOptions = $item->getProductOptions();
        if (!empty($productOptions['info_buyRequest']) && is_array($productOptions['info_buyRequest'])) {
            $requestData = $productOptions['info_buyRequest'];
        }

        if ($item->getQtyOrdered() !== null) {
            $requestData['qty'] = $item->getQtyOrdered();
        }

        return $requestData;
    }

    /**
     * Merge product option extension attributes into buy request data.
     *
     * @param array $requestData
     * @param object $extensionAttributes
     * @return array
     */
    private function applyExtensionAttributesToBuyRequest(array $requestData, object $extensionAttributes): array
    {
        if (method_exists($extensionAttributes, 'getCustomOptions')) {
            $requestData = $this->applyCustomOptionsToBuyRequest(
                $requestData,
                $extensionAttributes->getCustomOptions() ?: []
            );
        }

        if (method_exists($extensionAttributes, 'getConfigurableItemOptions')) {
            $requestData = $this->applyConfigurableOptionsToBuyRequest(
                $requestData,
                $extensionAttributes->getConfigurableItemOptions() ?: []
            );
        }

        if (method_exists($extensionAttributes, 'getBundleOptions')) {
            $requestData = $this->applyBundleOptionsToBuyRequest(
                $requestData,
                $extensionAttributes->getBundleOptions() ?: []
            );
        }

        return $requestData;
    }

    /**
     * Merge custom options into buy request data.
     *
     * @param array $requestData
     * @param array $customOptions
     * @return array
     */
    private function applyCustomOptionsToBuyRequest(array $requestData, array $customOptions): array
    {
        foreach ($customOptions as $option) {
            $requestData['options'][$option->getOptionId()] = $option->getOptionValue();
        }

        return $requestData;
    }

    /**
     * Merge configurable options into buy request data.
     *
     * @param array $requestData
     * @param array $configurableOptions
     * @return array
     */
    private function applyConfigurableOptionsToBuyRequest(array $requestData, array $configurableOptions): array
    {
        foreach ($configurableOptions as $option) {
            $requestData['super_attribute'][$option->getOptionId()] = (string)$option->getOptionValue();
        }

        return $requestData;
    }

    /**
     * Merge bundle options into buy request data.
     *
     * @param array $requestData
     * @param array $bundleOptions
     * @return array
     */
    private function applyBundleOptionsToBuyRequest(array $requestData, array $bundleOptions): array
    {
        foreach ($bundleOptions as $option) {
            foreach ($option->getOptionSelections() ?: [] as $selection) {
                $requestData['bundle_option'][$option->getOptionId()][] = $selection;
            }
            if ($option->getOptionQty() !== null) {
                $requestData['bundle_option_qty'][$option->getOptionId()] = $option->getOptionQty();
            }
        }

        return $requestData;
    }

    /**
     * Generate labels and product type metadata using catalog product type logic.
     *
     * @param OrderItemInterface $item
     * @param DataObject $buyRequest
     * @return array
     */
    private function getProductTypeOrderOptions(OrderItemInterface $item, DataObject $buyRequest): array
    {
        try {
            $product = $item->getProductId()
                ? $this->productRepository->getById((int)$item->getProductId(), false, $item->getStoreId(), true)
                : $this->productRepository->get((string)$item->getSku(), false, $item->getStoreId(), true);

            $result = $product->getTypeInstance()->prepareForCartAdvanced(
                $buyRequest,
                $product,
                AbstractType::PROCESS_MODE_LITE
            );
            if (!is_array($result)) {
                return [];
            }

            return $product->getTypeInstance()->getOrderOptions($product);
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Whether the order item carries a direct Web API product_option payload.
     *
     * @param OrderItemInterface $item
     * @return bool
     */
    private function hasProductOptionExtensionPayload(OrderItemInterface $item): bool
    {
        $extensionAttributes = $item->getProductOption()?->getExtensionAttributes();
        if (!$extensionAttributes) {
            return false;
        }

        if (method_exists($extensionAttributes, 'getCustomOptions') && $extensionAttributes->getCustomOptions()) {
            return true;
        }

        if (method_exists($extensionAttributes, 'getConfigurableItemOptions')
            && $extensionAttributes->getConfigurableItemOptions()
        ) {
            return true;
        }

        if (method_exists($extensionAttributes, 'getBundleOptions') && $extensionAttributes->getBundleOptions()) {
            return true;
        }

        return false;
    }

    /**
     * Add existing item product options to the order items before processing the relation.
     *
     * @param Relation $subject
     * @param AbstractModel $object
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeProcessRelation(Relation $subject, AbstractModel $object): void
    {
        if ($object instanceof OrderInterface && $object->getId() && $object->getItems()) {
            foreach ($object->getItems() as $item) {
                if ($item->getItemId()) {
                    $productOptions = $this->getExistingOrderItemProductOptions((int)$item->getItemId());
                    if (count($productOptions)) {
                        $item->setProductOptions($productOptions);
                    }
                    continue;
                }

                if (!$this->hasProductOptionExtensionPayload($item)) {
                    continue;
                }

                $productOptions = $this->getNewOrderItemProductOptions($item);
                if (count($productOptions)) {
                    $item->setProductOptions($productOptions);
                }
            }
        }
    }
}

<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Plugin\Model\ResourceModel\Order\Relation;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\ResourceModel\Order\Item as OrderItemResource;
use Magento\Sales\Model\ResourceModel\Order\Relation;

class AddExistingItemProductOptions
{
    /**
     * @param OrderItemResource $orderItemResource
     * @param Json $serializer
     */
    public function __construct(
        private readonly OrderItemResource $orderItemResource,
        private readonly Json $serializer
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
                }
            }
        }
    }
}

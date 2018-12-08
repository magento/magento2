<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Model\Order;

use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Framework\DataObject;
use Magento\Catalog\Model\ProductOptionFactory;
use Magento\Catalog\Model\ProductOptionProcessorInterface;
use Magento\Catalog\Api\Data\ProductOptionExtensionFactory;

/**
 * Adds product option to the order item according to product options processors pool.
<<<<<<< HEAD
=======
 *
 * @api
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
 */
class ProductOption
{
    /**
     * @var ProductOptionFactory
     */
    private $productOptionFactory;

    /**
     * @var ProductOptionExtensionFactory
     */
    private $extensionFactory;

    /**
     * @var ProductOptionProcessorInterface[]
     */
    private $processorPool;

    /**
     * @param ProductOptionFactory $productOptionFactory
     * @param ProductOptionExtensionFactory $extensionFactory
     * @param array $processorPool
     */
    public function __construct(
        ProductOptionFactory $productOptionFactory,
        ProductOptionExtensionFactory $extensionFactory,
        array $processorPool = []
    ) {
        $this->productOptionFactory = $productOptionFactory;
        $this->extensionFactory = $extensionFactory;
        $this->processorPool = $processorPool;
    }

    /**
     * Adds product option to the order item.
     *
     * @param OrderItemInterface $orderItem
<<<<<<< HEAD
     * @return void
     */
    public function add(OrderItemInterface $orderItem)
=======
     */
    public function add(OrderItemInterface $orderItem): void
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
    {
        /** @var DataObject $request */
        $request = $orderItem->getBuyRequest();

        $productType = $orderItem->getProductType();
        if (isset($this->processorPool[$productType])
            && !$orderItem->getParentItemId()) {
            $data = $this->processorPool[$productType]->convertToProductOption($request);
            if ($data) {
                $this->setProductOption($orderItem, $data);
            }
        }

        if (isset($this->processorPool['custom_options'])
            && !$orderItem->getParentItemId()) {
            $data = $this->processorPool['custom_options']->convertToProductOption($request);
            if ($data) {
                $this->setProductOption($orderItem, $data);
            }
        }
    }

    /**
     * Sets product options data.
     *
     * @param OrderItemInterface $orderItem
     * @param array $data
<<<<<<< HEAD
     * @return void
     */
    private function setProductOption(OrderItemInterface $orderItem, array $data)
=======
     */
    private function setProductOption(OrderItemInterface $orderItem, array $data): void
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
    {
        $productOption = $orderItem->getProductOption();
        if (!$productOption) {
            $productOption = $this->productOptionFactory->create();
            $orderItem->setProductOption($productOption);
        }

        $extensionAttributes = $productOption->getExtensionAttributes();
        if (!$extensionAttributes) {
            $extensionAttributes = $this->extensionFactory->create();
            $productOption->setExtensionAttributes($extensionAttributes);
        }

        $extensionAttributes->setData(key($data), current($data));
    }
}

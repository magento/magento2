<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Model;

use Magento\Catalog\Api\Data\ProductOptionExtensionFactory;
use Magento\Catalog\Model\ProductOptionFactory;
use Magento\ConfigurableProduct\Api\Data\ConfigurableItemOptionValueInterface;
use Magento\ConfigurableProduct\Model\Quote\Item\ConfigurableItemOptionValueFactory;
use Magento\Framework\DataObject;
use Magento\Framework\DataObject\Factory as DataObjectFactory;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Model\Order\Item\ProcessorInterface;

class ProductOptionProcessor implements ProcessorInterface
{
    /**
     * @var DataObjectFactory
     */
    protected $objectFactory;

    /**
     * @var ProductOptionFactory
     */
    protected $productOptionFactory;

    /**
     * @var ProductOptionExtensionFactory
     */
    protected $extensionFactory;

    /**
     * @var ConfigurableItemOptionValueFactory
     */
    protected $itemOptionValueFactory;

    /**
     * @param DataObjectFactory $objectFactory
     * @param ProductOptionFactory $productOptionFactory
     * @param ProductOptionExtensionFactory $extensionFactory
     * @param ConfigurableItemOptionValueFactory $itemOptionValueFactory
     */
    public function __construct(
        DataObjectFactory $objectFactory,
        ProductOptionFactory $productOptionFactory,
        ProductOptionExtensionFactory $extensionFactory,
        ConfigurableItemOptionValueFactory $itemOptionValueFactory
    ) {
        $this->objectFactory = $objectFactory;
        $this->productOptionFactory = $productOptionFactory;
        $this->extensionFactory = $extensionFactory;
        $this->itemOptionValueFactory = $itemOptionValueFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function convertToBuyRequest(OrderItemInterface $orderItem)
    {
        /** @var DataObject $request */
        $request = $this->objectFactory->create();

        $options = $this->getConfigurableItemOptions($orderItem);
        if (!empty($options)) {
            $requestData = [];
            foreach ($options as $option) {
                /** @var ConfigurableItemOptionValueInterface $option */
                $requestData['super_attribute'][$option->getOptionId()] = $option->getOptionValue();
            }
            $request->addData($requestData);
        }

        return $request;
    }

    /**
     * Retrieve configurable item options
     *
     * @param OrderItemInterface $orderItem
     * @return array
     */
    protected function getConfigurableItemOptions(OrderItemInterface $orderItem)
    {
        if ($orderItem->getProductOption()
            && $orderItem->getProductOption()->getExtensionAttributes()
            && $orderItem->getProductOption()->getExtensionAttributes()->getConfigurableItemOptions()
        ) {
            return $orderItem->getProductOption()
                ->getExtensionAttributes()
                ->getConfigurableItemOptions();
        }
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function processOptions(OrderItemInterface $orderItem)
    {
        $superAttribute = $orderItem->getBuyRequest()->getSuperAttribute();
        if (!empty($superAttribute) && is_array($superAttribute)) {
            $configurableItemOptions = [];
            foreach ($superAttribute as $optionId => $optionValue) {
                /** @var ConfigurableItemOptionValueInterface $option */
                $option = $this->itemOptionValueFactory->create();
                $option->setOptionId($optionId);
                $option->setOptionValue($optionValue);
                $configurableItemOptions[] = $option;
            }

            $this->setConfigurableItemOptions($orderItem, $configurableItemOptions);
        }

        return $orderItem;
    }

    /**
     * Set configurable item options
     *
     * @param OrderItemInterface $orderItem
     * @param ConfigurableItemOptionValueInterface[] $configurableItemOptions
     * @return $this
     */
    protected function setConfigurableItemOptions(OrderItemInterface $orderItem, array $configurableItemOptions)
    {
        if (!$orderItem->getProductOption()) {
            $productOption = $this->productOptionFactory->create();
            $orderItem->setProductOption($productOption);
        }

        if (!$orderItem->getProductOption()->getExtensionAttributes()) {
            $extensionAttributes = $this->extensionFactory->create();
            $orderItem->getProductOption()->setExtensionAttributes($extensionAttributes);
        }

        $orderItem->getProductOption()
            ->getExtensionAttributes()
            ->setConfigurableItemOptions($configurableItemOptions);

        return $this;
    }
}

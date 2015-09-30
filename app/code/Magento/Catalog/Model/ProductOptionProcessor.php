<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model;

use Magento\Catalog\Api\Data\ProductOptionExtensionFactory;
use Magento\Catalog\Model\CustomOptions\CustomOption;
use Magento\Catalog\Model\CustomOptions\CustomOptionFactory;
use Magento\Catalog\Model\ProductOptionFactory;
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
     * @var CustomOptionFactory
     */
    protected $customOptionFactory;

    /**
     * @param DataObjectFactory $objectFactory
     * @param \Magento\Catalog\Model\ProductOptionFactory $productOptionFactory
     * @param ProductOptionExtensionFactory $extensionFactory
     * @param CustomOptionFactory $customOptionFactory
     */
    public function __construct(
        DataObjectFactory $objectFactory,
        ProductOptionFactory $productOptionFactory,
        ProductOptionExtensionFactory $extensionFactory,
        CustomOptionFactory $customOptionFactory
    ) {
        $this->objectFactory = $objectFactory;
        $this->productOptionFactory = $productOptionFactory;
        $this->extensionFactory = $extensionFactory;
        $this->customOptionFactory = $customOptionFactory;
    }

    /**
     * @inheritDoc
     */
    public function convertToBuyRequest(OrderItemInterface $orderItem)
    {
        /** @var DataObject $request */
        $request = $this->objectFactory->create();

        $options = $this->getCustomOptions($orderItem);
        if (!empty($options)) {
            $requestData = [];
            foreach ($options as $option) {
                $requestData['options'][$option->getOptionId()] = $option->getOptionValue();
            }
            $request->addData($requestData);
        }

        return $request;
    }

    /**
     * Retrieve custom options
     *
     * @param OrderItemInterface $orderItem
     * @return array
     */
    protected function getCustomOptions(OrderItemInterface $orderItem)
    {
        if ($orderItem->getProductOption()
            && $orderItem->getProductOption()->getExtensionAttributes()
            && $orderItem->getProductOption()->getExtensionAttributes()->getCustomOptions()
        ) {
            return $orderItem->getProductOption()
                ->getExtensionAttributes()
                ->getCustomOptions();
        }
        return [];
    }

    /**
     * @inheritDoc
     */
    public function processOptions(OrderItemInterface $orderItem)
    {
        $options = $orderItem->getBuyRequest()->getOptions();
        if (!empty($options) && is_array($options)) {
            $customOptions = [];
            foreach ($options as $optionId => $optionValue) {
                if (is_array($optionValue)) {
                    $optionValue = implode(',', $optionValue);
                }

                /** @var CustomOption $option */
                $option = $this->customOptionFactory->create();
                $option->setOptionId($optionId)->setOptionValue($optionValue);
                $customOptions[] = $option;
            }

            $this->setCustomOptions($orderItem, $customOptions);
        }

        return $orderItem;
    }

    /**
     * Set custom options
     *
     * @param OrderItemInterface $orderItem
     * @param CustomOption[] $customOptions
     * @return $this
     */
    protected function setCustomOptions(OrderItemInterface $orderItem, array $customOptions)
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
            ->setCustomOptions($customOptions);

        return $this;
    }
}

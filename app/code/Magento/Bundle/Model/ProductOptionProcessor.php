<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Model;

use Magento\Bundle\Api\Data\BundleOptionInterface;
use Magento\Bundle\Api\Data\BundleOptionInterfaceFactory;
use Magento\Catalog\Api\Data\ProductOptionExtensionFactory;
use Magento\Catalog\Model\Product\Type as ProductType;
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
     * @var BundleOptionInterfaceFactory
     */
    protected $bundleOptionFactory;

    /**
     * @param DataObjectFactory $objectFactory
     * @param ProductOptionFactory $productOptionFactory
     * @param ProductOptionExtensionFactory $extensionFactory
     * @param BundleOptionInterfaceFactory $bundleOptionFactory
     */
    public function __construct(
        DataObjectFactory $objectFactory,
        ProductOptionFactory $productOptionFactory,
        ProductOptionExtensionFactory $extensionFactory,
        BundleOptionInterfaceFactory $bundleOptionFactory
    ) {
        $this->objectFactory = $objectFactory;
        $this->extensionFactory = $extensionFactory;
        $this->bundleOptionFactory = $bundleOptionFactory;
        $this->productOptionFactory = $productOptionFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function convertToBuyRequest(OrderItemInterface $orderItem)
    {
        /** @var DataObject $request */
        $request = $this->objectFactory->create();

        $bundleOptions = $this->getBundleOptions($orderItem);
        if (!empty($bundleOptions)) {
            $requestData = [];
            foreach ($bundleOptions as $option) {
                /** @var BundleOptionInterface $option */
                foreach ($option->getOptionSelections() as $selection) {
                    $requestData['bundle_option'][$option->getOptionId()][] = $selection;
                    $requestData['bundle_option_qty'][$option->getOptionId()] = $option->getOptionQty();
                }
            }
            $request->addData($requestData);
        }

        return $request;
    }

    /**
     * Retrieve bundle options
     *
     * @param OrderItemInterface $orderItem
     * @return array
     */
    protected function getBundleOptions(OrderItemInterface $orderItem)
    {
        if ($orderItem->getProductOption()
            && $orderItem->getProductOption()->getExtensionAttributes()
            && $orderItem->getProductOption()->getExtensionAttributes()->getBundleOptions()
        ) {
            return $orderItem->getProductOption()
                ->getExtensionAttributes()
                ->getBundleOptions();
        }
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function processOptions(OrderItemInterface $orderItem)
    {
        if ($orderItem->getProductType() !== ProductType::TYPE_BUNDLE) {
            return $orderItem;
        }

        $bundleOptions = $orderItem->getBuyRequest()->getBundleOption();
        $bundleOptionsQty = $orderItem->getBuyRequest()->getBundleOptionQty();

        $productOptions = [];
        foreach ($bundleOptions as $optionId => $optionSelections) {
            if (empty($optionSelections)) {
                continue;
            }
            $optionSelections = is_array($optionSelections) ? $optionSelections : [$optionSelections];
            $optionQty = isset($bundleOptionsQty[$optionId]) ? $bundleOptionsQty[$optionId] : 1;

            /** @var BundleOptionInterface $productOption */
            $productOption = $this->bundleOptionFactory->create();
            $productOption->setOptionId($optionId);
            $productOption->setOptionSelections($optionSelections);
            $productOption->setOptionQty($optionQty);
            $productOptions[] = $productOption;
        }

        $this->setBundleOptions($orderItem, $productOptions);

        return $orderItem;
    }

    /**
     * Set bundle options
     *
     * @param OrderItemInterface $orderItem
     * @param BundleOptionInterface[] $bundleOptions
     * @return $this
     */
    protected function setBundleOptions(OrderItemInterface $orderItem, array $bundleOptions)
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
            ->setBundleOptions($bundleOptions);

        return $this;
    }
}

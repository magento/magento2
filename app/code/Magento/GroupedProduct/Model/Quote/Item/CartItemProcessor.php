<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GroupedProduct\Model\Quote\Item;

use Magento\Framework\DataObject;
use Magento\Framework\DataObject\Factory as ObjectFactory;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\GroupedProduct\Api\Data\GroupedOptionsInterface;
use Magento\GroupedProduct\Api\Data\GroupedOptionsInterfaceFactory;
use Magento\GroupedProduct\Model\Product\Type\Grouped;
use Magento\Quote\Api\Data as QuoteApi;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Model\Quote\Item\CartItemProcessorInterface;

/**
 * Converts grouped_options to super_group for the grouped product.
 */
class CartItemProcessor implements CartItemProcessorInterface
{
    private const SUPER_GROUP_CODE = 'super_group';

    /**
     * @var ObjectFactory
     */
    private $objectFactory;

    /**
     * @var GroupedOptionsInterface
     */
    private $groupedOptionFactory;

    /**
     * @var Json
     */
    private $jsonSerializer;

    /**
     * @var QuoteApi\ProductOptionExtensionFactory
     */
    private $productOptionExtensionFactory;

    /**
     * @var QuoteApi\ProductOptionInterfaceFactory
     */
    private $productOptionFactory;

    /**
     * @param ObjectFactory $objectFactory
     * @param GroupedOptionsInterfaceFactory $groupedOptionFactory
     * @param Json $jsonSerializer
     * @param QuoteApi\ProductOptionExtensionFactory $productOptionExtensionFactory
     * @param QuoteApi\ProductOptionInterfaceFactory $productOptionFactory
     */
    public function __construct(
        ObjectFactory $objectFactory,
        GroupedOptionsInterfaceFactory $groupedOptionFactory,
        Json $jsonSerializer,
        QuoteApi\ProductOptionExtensionFactory $productOptionExtensionFactory,
        QuoteApi\ProductOptionInterfaceFactory $productOptionFactory
    ) {
        $this->objectFactory = $objectFactory;
        $this->groupedOptionFactory = $groupedOptionFactory;
        $this->jsonSerializer = $jsonSerializer;
        $this->productOptionExtensionFactory = $productOptionExtensionFactory;
        $this->productOptionFactory = $productOptionFactory;
    }

    /**
     * Converts the grouped_options request data into the same format as native frontend add-to-cart
     *
     * @param CartItemInterface $cartItem
     * @return DataObject|null
     */
    public function convertToBuyRequest(CartItemInterface $cartItem): ?DataObject
    {
        $extensionAttributes = $cartItem->getProductOption()
            ? $cartItem->getProductOption()->getExtensionAttributes()
            : null;
        if ($extensionAttributes) {
            $groupedOptions = $extensionAttributes->getGroupedOptions();
            if ($groupedOptions) {
                $requestData = [];

                foreach ($groupedOptions as $item) {
                    $requestData[self::SUPER_GROUP_CODE][$item->getId()] = $item->getQty();
                }

                return $this->objectFactory->create($requestData);
            }
        }

        return null;
    }

    /**
     * Option processor
     *
     * @param CartItemInterface $cartItem
     * @return CartItemInterface
     */
    public function processOptions(CartItemInterface $cartItem): CartItemInterface
    {
        if ($cartItem->getProductType() !== Grouped::TYPE_CODE) {
            return $cartItem;
        }

        $superGroup = $cartItem->getOptionByCode(self::SUPER_GROUP_CODE);
        $superGroupValues = $superGroup ? $this->jsonSerializer->unserialize($superGroup->getValue()) : null;
        if ($superGroupValues) {
            $productOptions = [];
            foreach ($superGroupValues as $id => $qty) {
                $productOptions[] = $this->groupedOptionFactory->create(['id' => $id, 'qty' => $qty]);
            }

            $extension = $this->productOptionExtensionFactory->create()->setGroupedOptions($productOptions);
            if (!$cartItem->getProductOption()) {
                $cartItem->setProductOption($this->productOptionFactory->create());
            }
            $cartItem->getProductOption()->setExtensionAttributes($extension);
        }

        return $cartItem;
    }
}

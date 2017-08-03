<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Model\Quote\Item;

use Magento\Quote\Model\Quote\Item\CartItemProcessorInterface;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\App\ObjectManager;

/**
 * Class \Magento\ConfigurableProduct\Model\Quote\Item\CartItemProcessor
 *
 * @since 2.0.0
 */
class CartItemProcessor implements CartItemProcessorInterface
{
    /**
     * @var \Magento\Framework\DataObject\Factory
     * @since 2.0.0
     */
    protected $objectFactory;

    /**
     * @var \Magento\Quote\Model\Quote\ProductOptionFactory
     * @since 2.0.0
     */
    protected $productOptionFactory;

    /**
     * @var \Magento\Quote\Api\Data\ProductOptionExtensionFactory
     * @since 2.0.0
     */
    protected $extensionFactory;

    /**
     * @var \Magento\ConfigurableProduct\Model\Quote\Item\ConfigurableItemOptionValueFactory
     * @since 2.0.0
     */
    protected $itemOptionValueFactory;

    /**
     * @var Json
     * @since 2.2.0
     */
    private $serializer;

    /**
     * @param \Magento\Framework\DataObject\Factory $objectFactory
     * @param \Magento\Quote\Model\Quote\ProductOptionFactory $productOptionFactory
     * @param \Magento\Quote\Api\Data\ProductOptionExtensionFactory $extensionFactory
     * @param \Magento\ConfigurableProduct\Model\Quote\Item\ConfigurableItemOptionValueFactory $itemOptionValueFactory
     * @param Json $serializer
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\DataObject\Factory $objectFactory,
        \Magento\Quote\Model\Quote\ProductOptionFactory $productOptionFactory,
        \Magento\Quote\Api\Data\ProductOptionExtensionFactory $extensionFactory,
        \Magento\ConfigurableProduct\Model\Quote\Item\ConfigurableItemOptionValueFactory $itemOptionValueFactory,
        Json $serializer = null
    ) {
        $this->objectFactory = $objectFactory;
        $this->productOptionFactory = $productOptionFactory;
        $this->extensionFactory = $extensionFactory;
        $this->itemOptionValueFactory = $itemOptionValueFactory;
        $this->serializer = $serializer ?: ObjectManager::getInstance()->get(Json::class);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function convertToBuyRequest(CartItemInterface $cartItem)
    {
        if ($cartItem->getProductOption() && $cartItem->getProductOption()->getExtensionAttributes()) {
            /** @var \Magento\ConfigurableProduct\Api\Data\ConfigurableItemOptionValueInterface $options */
            $options = $cartItem->getProductOption()->getExtensionAttributes()->getConfigurableItemOptions();
            if (is_array($options)) {
                $requestData = [];
                foreach ($options as $option) {
                    $requestData['super_attribute'][$option->getOptionId()] = $option->getOptionValue();
                }
                return $this->objectFactory->create($requestData);
            }
        }
        return null;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function processOptions(CartItemInterface $cartItem)
    {
        $attributesOption = $cartItem->getProduct()->getCustomOption('attributes');
        $selectedConfigurableOptions = $this->serializer->unserialize($attributesOption->getValue());

        if (is_array($selectedConfigurableOptions)) {
            $configurableOptions = [];
            foreach ($selectedConfigurableOptions as $optionId => $optionValue) {
                /** @var \Magento\ConfigurableProduct\Api\Data\ConfigurableItemOptionValueInterface $option */
                $option = $this->itemOptionValueFactory->create();
                $option->setOptionId($optionId);
                $option->setOptionValue($optionValue);
                $configurableOptions[] = $option;
            }

            $productOption = $cartItem->getProductOption()
                ? $cartItem->getProductOption()
                : $this->productOptionFactory->create();

            /** @var  \Magento\Quote\Api\Data\ProductOptionExtensionInterface $extensibleAttribute */
            $extensibleAttribute =  $productOption->getExtensionAttributes()
                ? $productOption->getExtensionAttributes()
                : $this->extensionFactory->create();

            $extensibleAttribute->setConfigurableItemOptions($configurableOptions);
            $productOption->setExtensionAttributes($extensibleAttribute);
            $cartItem->setProductOption($productOption);
        }
        return $cartItem;
    }
}

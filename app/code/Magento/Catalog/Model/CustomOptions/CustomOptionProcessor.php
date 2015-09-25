<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\CustomOptions;

use Magento\Framework\DataObject;
use Magento\Quote\Api\Data\CartItemInterface;

class CustomOptionProcessor implements CustomOptionProcessorInterface
{
    /**
     * @var \Magento\Framework\DataObject\Factory
     */
    protected $objectFactory;

    /**
     * @var \Magento\Quote\Model\Quote\ProductOptionFactory
     */
    protected $productOptionFactory;

    /**
     * @var \Magento\Quote\Api\Data\ProductOptionExtensionFactory
     */
    protected $extensionFactory;

    /**
     * @var \Magento\ConfigurableProduct\Model\Quote\Item\ConfigurableItemOptionValueFactory
     */
    protected $customOptionFactory;

    /**
     * @param \Magento\Framework\DataObject\Factory $objectFactory
     * @param \Magento\Quote\Model\Quote\ProductOptionFactory $productOptionFactory
     * @param \Magento\Quote\Api\Data\ProductOptionExtensionFactory $extensionFactory
     * @param \Magento\Catalog\Model\CustomOptions\CustomOptionFactory $customOptionFactory
     */
    public function __construct(
        \Magento\Framework\DataObject\Factory $objectFactory,
        \Magento\Quote\Model\Quote\ProductOptionFactory $productOptionFactory,
        \Magento\Quote\Api\Data\ProductOptionExtensionFactory $extensionFactory,
        \Magento\Catalog\Model\CustomOptions\CustomOptionFactory $customOptionFactory
    ) {
        $this->objectFactory = $objectFactory;
        $this->productOptionFactory = $productOptionFactory;
        $this->extensionFactory = $extensionFactory;
        $this->customOptionFactory = $customOptionFactory;
    }

    /**
     * @inheritDoc
     */
    public function convertToBuyRequest(CartItemInterface $cartItem)
    {
        $buyRequest = $this->objectFactory->create();
        if ($cartItem->getProductOption()) {
            /** @var \Magento\Catalog\Api\Data\CustomOptionInterface[] $options */
            $options = $cartItem->getProductOption()->getExtensionAttributes()->getCustomOptions();
            if (is_array($options)) {
                $requestData = [];
                foreach ($options as $option) {
                    $requestData['options'][$option->getOptionId()] = $option->getOptionValue();
                }
                $buyRequest->setData($requestData);
            }
        }
        return $buyRequest;
    }

    /**
     * @inheritDoc
     */
    public function processCustomOptions(CartItemInterface $cartItem)
    {
        $buyRequest = unserialize($cartItem->getOptionByCode('info_buyRequest')->getValue());
        $options = isset($buyRequest['options']) ? $buyRequest['options'] : [];
        if (is_array($options) ) {
            foreach ($options as $optionId => &$optionValue) {
                /** @var \Magento\Catalog\Model\CustomOptions\CustomOption $option */
                $option = $this->customOptionFactory->create();
                $option->setOptionId($optionId);
                if (is_array($optionValue)) {
                    $optionValue = implode(',', $optionValue);
                }
                $option->setOptionValue($optionValue);
                $optionValue = $option;
            }

            $productOption = $cartItem->getProductOption()
                ? $cartItem->getProductOption()
                : $this->productOptionFactory->create();

            /** @var  \Magento\Quote\Api\Data\ProductOptionExtensionInterface $extensibleAttribute */
            $extensibleAttribute =  $productOption->getExtensionAttributes()
                ? $productOption->getExtensionAttributes()
                : $this->extensionFactory->create();

            $extensibleAttribute->setCustomOptions($options);
            $productOption->setExtensionAttributes($extensibleAttribute);
            $cartItem->setProductOption($productOption);
        }
        return $cartItem;
    }
}

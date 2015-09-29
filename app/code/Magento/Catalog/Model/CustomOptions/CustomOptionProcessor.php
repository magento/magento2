<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\CustomOptions;

use Magento\Framework\DataObject;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Model\Quote\Item\CartItemProcessorInterface;

class CustomOptionProcessor implements CartItemProcessorInterface
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
     * @var \Magento\Catalog\Model\CustomOptions\CustomOptionFactory
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
            $extensionAttributes= $cartItem->getProductOption()->getExtensionAttributes();
            if ($extensionAttributes && is_array($extensionAttributes->getCustomOptions())) {
                $requestData = [];
                foreach ($extensionAttributes->getCustomOptions() as $option) {
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
    public function processOptions(CartItemInterface $cartItem)
    {

        if (is_array($options)) {
            $this->updateOptionsValues($options);
            $productOption = $cartItem->getProductOption()
                ? $cartItem->getProductOption()
                : $this->productOptionFactory->create();

            /** @var  \Magento\Quote\Api\Data\ProductOptionExtensionInterface $extensibleAttribute */
            $extensibleAttribute = $productOption->getExtensionAttributes()
                ? $productOption->getExtensionAttributes()
                : $this->extensionFactory->create();

            $extensibleAttribute->setCustomOptions($options);
            $productOption->setExtensionAttributes($extensibleAttribute);
            $cartItem->setProductOption($productOption);
        }
        return $cartItem;
    }

    /**
     * Receive custom option from buy request
     *
     * @param CartItemInterface $cartItem
     * @return array
     */
    protected function getOptions(CartItemInterface $cartItem)
    {
        $buyRequest = !empty($cartItem->getOptionByCode('info_buyRequest'))
            ? unserialize($cartItem->getOptionByCode('info_buyRequest')->getValue())
            : null;
        return is_array($buyRequest) && isset($buyRequest['options'])
            ? $buyRequest['options']
            : [];
    }

    /**
     * Update options values
     *
     * @param array $options
     * @return null
     */
    protected function updateOptionsValues(array &$options)
    {
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
    }
}

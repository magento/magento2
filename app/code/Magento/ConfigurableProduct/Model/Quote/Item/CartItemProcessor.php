<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Model\Quote\Item;

use Magento\Quote\Model\Quote\Item\CartItemProcessorInterface;
use Magento\Quote\Api\Data\CartItemInterface;

class CartItemProcessor implements CartItemProcessorInterface
{
    /**
     * @var \Magento\Framework\DataObject\Factory
     */
    protected $objectFactory;

    /**
     * @param \Magento\Framework\DataObject\Factory $objectFactory
     */
    public function __construct(\Magento\Framework\DataObject\Factory $objectFactory)
    {
        $this->objectFactory = $objectFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function convertToBuyRequest(CartItemInterface $cartItem)
    {
        if ($cartItem->getProductOption()) {
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
     */
    public function processProductOptions(CartItemInterface $cartItem)
    {
        return $cartItem;
    }
}

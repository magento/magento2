<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Model;

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
            $options = $cartItem->getProductOption()->getExtensionAttributes()->getBundleOptions();
            if (is_array($options)) {
                $requestData = [];
                foreach ($options as $option) {
                    /** @var \Magento\Bundle\Api\Data\BundleOptionInterface $option */
                    foreach($option->getOptionSelections() as $selection) {
                        $requestData['bundle_option'][$option->getOptionId()][] = $selection;
                        $requestData['bundle_option_qty'][$option->getOptionId()] =  $option->getOptionQty();
                    }
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

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GroupedProduct\Model\Quote\Item;

use Magento\Framework\DataObject\Factory as ObjectFactory;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Api\Data\ProductOptionInterface;
use Magento\Quote\Model\Quote\Item\CartItemProcessorInterface;

/**
 * A class that converts the Grouped Product super group, as received over RESTful API,
 * into the format needed within the buy request
 *
 * Class \Magento\GroupedProduct\Model\Quote\Item\CartItemProcessor
 */
class CartItemProcessor implements CartItemProcessorInterface
{
    /**
     * @var ObjectFactory
     */
    private $objectFactory;

    /**
     * CartItemProcessor constructor.
     *
     * @param ObjectFactory $objectFactory
     */
    public function __construct(ObjectFactory $objectFactory)
    {
        $this->objectFactory = $objectFactory;
    }

    /**
     * Converts the super_group request data into the same format as native frontend add-to-cart
     *
     * @param CartItemInterface $cartItem
     *
     * @return \Magento\Framework\DataObject|null
     */
    public function convertToBuyRequest(CartItemInterface $cartItem)
    {
        $productOption = $cartItem->getProductOption();
        if ($productOption instanceof ProductOptionInterface && $productOption->getExtensionAttributes()) {
            $superGroup = $productOption->getExtensionAttributes()->getSuperGroup();
            if (is_array($superGroup)) {
                $requestData = [];

                /** @var GroupedItemQty $item */
                foreach ($superGroup as $item) {
                    if (!isset($requestData['super_group'])) {
                        $requestData['super_group'] = [];
                    }

                    $requestData['super_group'][$item->getProductId()] = $item->getQty();
                }

                if (!empty($requestData)) {
                    return $this->objectFactory->create($requestData);
                }
            }
        }

        return null;
    }

    /**
     * Option processor
     *
     * @param CartItemInterface $cartItem
     *
     * @return CartItemInterface
     */
    public function processOptions(CartItemInterface $cartItem)
    {
        return $cartItem;
    }
}

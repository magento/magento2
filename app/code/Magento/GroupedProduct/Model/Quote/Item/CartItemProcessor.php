<?php

namespace Magento\GroupedProduct\Model\Quote\Item;

use Magento\Framework\DataObject\Factory as ObjectFactory;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Api\Data\ProductOptionInterface;
use Magento\Quote\Model\Quote\Item\CartItemProcessorInterface;

/**
 * Class CartItemProcessor
 */
class CartItemProcessor implements CartItemProcessorInterface
{
    /**
     * @var ObjectFactory
     */
    protected $objectFactory;

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
     * @param CartItemInterface $cartItem
     *
     * @return CartItemInterface
     */
    public function processOptions(CartItemInterface $cartItem)
    {
        return $cartItem;
    }
}

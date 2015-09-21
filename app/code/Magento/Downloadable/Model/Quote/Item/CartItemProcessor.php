<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Model\Quote\Item;

use Magento\Quote\Model\Quote\Item\CartItemProcessorInterface;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Framework\DataObject\Factory as DataObjectFactory;

class CartItemProcessor implements CartItemProcessorInterface
{
    /**
     * @var DataObjectFactory
     */
    private $objectFactory;

    /**
     * @param DataObjectFactory $objectFactory
     */
    public function __construct(DataObjectFactory $objectFactory)
    {
        $this->objectFactory = $objectFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function convertToBuyRequest(CartItemInterface $cartItem)
    {
        if ($cartItem->getProductOption()
            && $cartItem->getProductOption()->getExtensionAttributes()->getDownloadableOption()
        ) {
            $downloadableLinks = $cartItem->getProductOption()->getExtensionAttributes()->getDownloadableOption()
                ->getDownloadableLinks();
            if (!empty($downloadableLinks)) {
                return $this->objectFactory->create([
                    'links' => $downloadableLinks,
                    'qty' => $cartItem->getQty(),
                ]);
            }
        }
        throw new \Exception('Cart item does not contain downloadable links.');
    }

    /**
     * Process cart item product options
     *
     * @param CartItemInterface $cartItem
     * @return CartItemInterface
     */
    public function processProductOptions(CartItemInterface $cartItem)
    {
        return $cartItem;
    }

}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Model\Plugin;

use Magento\Quote\Model\Quote\Item as OrigQuoteItem;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * Update prices stored in quote item options after calculating quote item's totals.
 */
class UpdatePriceInQuoteItemOptions
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @param SerializerInterface $serializer
     */
    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * Update price on quote item options level
     *
     * @param OrigQuoteItem $subject
     * @param AbstractItem $result
     * @return AbstractItem
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterCalcRowTotal(OrigQuoteItem $subject, AbstractItem $result): AbstractItem
    {
        $bundleAttributes = $result->getProduct()->getCustomOption('bundle_selection_attributes');
        if ($bundleAttributes !== null) {
            $actualAmount = $result->getPrice() * $result->getQty();
            $parsedValue = $this->serializer->unserialize($bundleAttributes->getValue());
            if (is_array($parsedValue) && array_key_exists('price', $parsedValue)) {
                $parsedValue['price'] = $actualAmount;
            }
            $bundleAttributes->setValue($this->serializer->serialize($parsedValue));
        }

        return $result;
    }
}

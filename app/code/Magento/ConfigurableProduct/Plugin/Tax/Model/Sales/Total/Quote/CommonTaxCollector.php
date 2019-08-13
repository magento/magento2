<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Plugin\Tax\Model\Sales\Total\Quote;

use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\Tax\Api\Data\QuoteDetailsItemInterface;
use Magento\Tax\Api\Data\QuoteDetailsItemInterfaceFactory;

/**
 * Plugin for CommonTaxCollector to apply Tax Class ID from child item for configurable product
 */
class CommonTaxCollector
{
    /**
     * Apply Tax Class ID from child item for configurable product
     *
     * @param \Magento\Tax\Model\Sales\Total\Quote\CommonTaxCollector $subject
     * @param QuoteDetailsItemInterface $result
     * @param QuoteDetailsItemInterfaceFactory $itemDataObjectFactory
     * @param AbstractItem $item
     * @return QuoteDetailsItemInterface
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterMapItem(
        \Magento\Tax\Model\Sales\Total\Quote\CommonTaxCollector $subject,
        QuoteDetailsItemInterface $result,
        QuoteDetailsItemInterfaceFactory $itemDataObjectFactory,
        AbstractItem $item
    ) : QuoteDetailsItemInterface {
        if ($item->getProduct()->getTypeId() === Configurable::TYPE_CODE && $item->getHasChildren()) {
            $childItem = $item->getChildren()[0];
            $result->getTaxClassKey()->setValue($childItem->getProduct()->getTaxClassId());
        }

        return $result;
    }
}

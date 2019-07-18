<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Model\Product\Plugin;

use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Action as ProductAction;
use Magento\Quote\Model\ResourceModel\Quote as QuoteResource;

/**
 * Remove quote items after mass disabling products
 */
class MarkQuotesRecollectMassDisabled
{
    /** @var QuoteResource$quoteResource */
    private $quoteResource;

    /**
     * @param QuoteResource $quoteResource
     */
    public function __construct(
        QuoteResource $quoteResource
    ) {
        $this->quoteResource = $quoteResource;
    }

    /**
     * Clean quote items after mass disabling product
     *
     * @param \Magento\Catalog\Model\Product\Action $subject
     * @param \Magento\Catalog\Model\Product\Action $result
     * @param int[] $productIds
     * @param int[] $attrData
     * @param int $storeId
     * @return \Magento\Catalog\Model\Product\Action
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterUpdateAttributes(
        ProductAction $subject,
        ProductAction $result,
        $productIds,
        $attrData,
        $storeId
    ): ProductAction {
        if (isset($attrData['status']) && $attrData['status'] === Status::STATUS_DISABLED) {
            $this->quoteResource->markQuotesRecollect($productIds);
        }

        return $result;
    }
}

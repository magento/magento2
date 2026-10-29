<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Model\Order\Webapi;

use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Block\Adminhtml\Items\Column\DefaultColumn;
use Magento\Sales\Block\Order\Item\Renderer\DefaultRenderer;
use Magento\Bundle\Model\Product\Type;

/**
 * Class for changing row total in response.
 */
class ChangeOutputArray
{
    /**
     * @var DefaultColumn
     */
    private $priceRenderer;

    /**
     * @var DefaultRenderer
     */
    private $defaultRenderer;

    /**
     * @param DefaultColumn $priceRenderer
     * @param DefaultRenderer $defaultRenderer
     */
    public function __construct(
        DefaultColumn $priceRenderer,
        DefaultRenderer $defaultRenderer
    ) {
        $this->priceRenderer = $priceRenderer;
        $this->defaultRenderer = $defaultRenderer;
    }

    /**
     * Changing row total for webapi order item response.
     *
     * @param OrderItemInterface $dataObject
     * @param array $result
     * @return array
     */
    public function execute(
        OrderItemInterface $dataObject,
        array $result
    ): array {
        $result[OrderItemInterface::ROW_TOTAL] = $this->round($this->priceRenderer->getTotalAmount($dataObject));
        $result[OrderItemInterface::BASE_ROW_TOTAL] = $this->round(
            $this->priceRenderer->getBaseTotalAmount($dataObject)
        );
        $result[OrderItemInterface::ROW_TOTAL_INCL_TAX] = $this->round(
            $this->defaultRenderer->getTotalAmount($dataObject)
        );
        $result[OrderItemInterface::BASE_ROW_TOTAL_INCL_TAX] = $this->round($dataObject->getBaseRowTotal()
            + $dataObject->getBaseTaxAmount()
            + $dataObject->getBaseDiscountTaxCompensationAmount()
            + $dataObject->getBaseWeeeTaxAppliedAmount()
            - $dataObject->getBaseDiscountAmount());

        return $this->getBundleProductPrice($result);
    }

    /**
     * Remove negative values from row totals
     *
     * @param float $value
     * @return float
     */
    private function round(float $value): float
    {
        return (float) max(round($value, 2), 0);
    }

    /**
     * Get bundle product price if miss in child item
     *
     * @param array $result
     * @return array
     */
    private function getBundleProductPrice(array $result): array
    {
        if (isset($result[OrderItemInterface::PRICE], $result[OrderItemInterface::PARENT_ITEM]) &&
            $result[OrderItemInterface::PRICE] == 0
        ) {
            if ($result[OrderItemInterface::PARENT_ITEM][OrderItemInterface::PRODUCT_TYPE] == Type::TYPE_CODE) {
                $result[OrderItemInterface::PRICE] =
                    $result[OrderItemInterface::PARENT_ITEM][OrderItemInterface::PRICE];
            }
        }

        return $result;
    }
}

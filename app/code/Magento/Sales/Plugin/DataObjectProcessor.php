<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Plugin;

use Magento\Framework\Reflection\DataObjectProcessor as Subject;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Model\Order\Item as OrderItem;
use Magento\Weee\Block\Item\Price\Renderer;

/**
 * Class for changing row total in response.
 */
class DataObjectProcessor
{
    /**
     * @var Renderer
     */
    private $priceRenderer;

    /**
     * @param Renderer $priceRenderer
     */
    public function __construct(
        Renderer $priceRenderer
    ) {
        $this->priceRenderer = $priceRenderer;
    }

    /**
     * Changing row total for webapi order item response.
     *
     * @param Subject $subject
     * @param array $result
     * @param mixed $dataObject
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterBuildOutputDataArray(
        Subject $subject,
        $result,
        $dataObject
    ) {
        if ($dataObject instanceof OrderItem) {
            $result[OrderItemInterface::ROW_TOTAL] = $this->priceRenderer->getTotalAmount($dataObject);
            $result[OrderItemInterface::BASE_ROW_TOTAL] = $this->priceRenderer->getBaseTotalAmount($dataObject);
        }

        return $result;
    }
}

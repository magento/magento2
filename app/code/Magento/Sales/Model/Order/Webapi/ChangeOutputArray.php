<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Model\Order\Webapi;

use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Block\Adminhtml\Items\Column\DefaultColumn;

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
     * @param DefaultColumn $priceRenderer
     */
    public function __construct(
        DefaultColumn $priceRenderer
    ) {
        $this->priceRenderer = $priceRenderer;
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
        $result[OrderItemInterface::ROW_TOTAL] = $this->priceRenderer->getTotalAmount($dataObject);
        $result[OrderItemInterface::BASE_ROW_TOTAL] = $this->priceRenderer->getBaseTotalAmount($dataObject);

        return $result;
    }
}

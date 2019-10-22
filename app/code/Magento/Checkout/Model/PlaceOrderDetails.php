<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Model;

use Magento\Checkout\Api\Data\PlaceOrderDetailsInterface;
use Magento\Quote\Api\Data\TotalsInterface;

/**
 * Check order id if successfully placed order, in other cases use other fields
 *
 * @codeCoverageIgnoreStart
 */
class PlaceOrderDetails extends \Magento\Framework\Api\AbstractSimpleObject implements PlaceOrderDetailsInterface
{

    /**
     * Initialize payment details
     *
     * PlaceOrderDetails constructor.
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        parent::__construct($data);
        $this->_data[self::ERRORS] = [];
    }

    /**
     * @inheritdoc
     */
    public function setOrderId(int $orderId): PlaceOrderDetailsInterface
    {
        return $this->setData(self::ORDER_ID, $orderId);
    }

    /**
     * @inheritdoc
     */
    public function getOrderId(): int
    {
        return $this->_get(self::ORDER_ID) ?? 0;
    }

    /**
     * @inheritdoc
     */
    public function getErrors(): array
    {
        return $this->_get(self::ERRORS);
    }

    /**
     * @inheritdoc
     */
    public function hasErrors(): bool
    {
        return count($this->_get(self::ERRORS)) > 0;
    }

    /**
     * @inheritdoc
     */
    public function addError(string $error): PlaceOrderDetailsInterface
    {
        $this->_data[self::ERRORS][] = $error;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getTotals(): ?TotalsInterface
    {
        return $this->_get(self::TOTALS);
    }

    /**
     * @inheritdoc
     */
    public function hasTotals(): bool
    {
        return isset($this->_data[self::TOTALS]);
    }

    /**
     * @inheritdoc
     */
    public function setTotals($totals): PlaceOrderDetailsInterface
    {
        return $this->setData(self::TOTALS, $totals);
    }
}

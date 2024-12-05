<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order;

/**
 * @method int getOrderId()
 * @method \Magento\Sales\Model\Order\Tax setOrderId(int $value)
 * @method string getCode()
 * @method \Magento\Sales\Model\Order\Tax setCode(string $value)
 * @method string getTitle()
 * @method \Magento\Sales\Model\Order\Tax setTitle(string $value)
 * @method float getPercent()
 * @method \Magento\Sales\Model\Order\Tax setPercent(float $value)
 * @method float getAmount()
 * @method \Magento\Sales\Model\Order\Tax setAmount(float $value)
 * @method int getPriority()
 * @method \Magento\Sales\Model\Order\Tax setPriority(int $value)
 * @method int getPosition()
 * @method \Magento\Sales\Model\Order\Tax setPosition(int $value)
 * @method float getBaseAmount()
 * @method \Magento\Sales\Model\Order\Tax setBaseAmount(float $value)
 * @method int getProcess()
 * @method \Magento\Sales\Model\Order\Tax setProcess(int $value)
 * @method float getBaseRealAmount()
 * @method \Magento\Sales\Model\Order\Tax setBaseRealAmount(float $value)
 */
class Tax extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Magento\Sales\Model\ResourceModel\Order\Tax::class);
    }
}

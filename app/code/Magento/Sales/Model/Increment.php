<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model;

use Magento\Eav\Model\Config as EavConfig;

/**
 * Class Increment
 */
class Increment
{
    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $eavConfig;

    /**
     * @var string
     */
    protected $incrementValue;

    /**
     * @param EavConfig $eavConfig
     */
    public function __construct(
        EavConfig $eavConfig
    ) {
        $this->eavConfig = $eavConfig;
    }

    /**
     * Returns current increment id
     *
     * @return string
     */
    public function getCurrentValue()
    {
        return $this->incrementValue;
    }

    /**
     * Returns new value of increment id
     *
     * @param int $storeId
     * @return string
     * @throws \Exception
     * @throws \Magento\Framework\Model\Exception
     */
    public function getNextValue($storeId)
    {
        $this->incrementValue =
            $this->eavConfig->getEntityType(Order::ENTITY)->fetchNewIncrementId($storeId);
        return $this->incrementValue;
    }
}

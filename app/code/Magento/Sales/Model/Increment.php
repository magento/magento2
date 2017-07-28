<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model;

use Magento\Eav\Model\Config as EavConfig;

/**
 * Class Increment
 * @deprecated 2.2.0
 * @since 2.0.0
 */
class Increment
{
    /**
     * @var \Magento\Eav\Model\Config
     * @since 2.0.0
     */
    protected $eavConfig;

    /**
     * @var string
     * @since 2.0.0
     */
    protected $incrementValue;

    /**
     * @param EavConfig $eavConfig
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @throws \Magento\Framework\Exception\LocalizedException
     * @since 2.0.0
     */
    public function getNextValue($storeId)
    {
        $this->incrementValue =
            $this->eavConfig->getEntityType(Order::ENTITY)->fetchNewIncrementId($storeId);
        return $this->incrementValue;
    }
}

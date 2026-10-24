<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */

namespace Magento\Catalog\Model\Product\Price;

use Magento\Catalog\Api\Data\PriceUpdateResultInterface;

/**
 * {@inheritdoc}
 */
class PriceUpdateResult extends \Magento\Framework\Model\AbstractExtensibleModel implements PriceUpdateResultInterface
{
    /**
     * {@inheritdoc}
     */
    public function getMessage()
    {
        return $this->getData(self::MESSAGE);
    }

    /**
     * {@inheritdoc}
     */
    public function setMessage($message)
    {
        return $this->setData(self::MESSAGE, $message);
    }

    /**
     * {@inheritdoc}
     */
    public function getParameters()
    {
        return $this->getData(self::PARAMETERS);
    }

    /**
     * {@inheritdoc}
     */
    public function setParameters(array $parameters)
    {
        return $this->setData(self::PARAMETERS, $parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * {@inheritdoc}
     */
    public function setExtensionAttributes(
        \Magento\Catalog\Api\Data\PriceUpdateResultExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}

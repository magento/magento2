<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Product\Price;

use Magento\Catalog\Api\Data\PriceUpdateResultInterface;

/**
 * {@inheritdoc}
 * @since 2.2.0
 */
class PriceUpdateResult extends \Magento\Framework\Model\AbstractExtensibleModel implements PriceUpdateResultInterface
{
    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function getMessage()
    {
        return $this->getData(self::MESSAGE);
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function setMessage($message)
    {
        return $this->setData(self::MESSAGE, $message);
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function getParameters()
    {
        return $this->getData(self::PARAMETERS);
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function setParameters(array $parameters)
    {
        return $this->setData(self::PARAMETERS, $parameters);
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function setExtensionAttributes(
        \Magento\Catalog\Api\Data\PriceUpdateResultExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}

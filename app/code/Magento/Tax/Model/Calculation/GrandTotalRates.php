<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Model\Calculation;

use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\Tax\Api\Data\GrandTotalRatesInterface;

/**
 * Grand Total Tax Details Model
 */
class GrandTotalRates extends AbstractExtensibleModel implements GrandTotalRatesInterface
{

    /**#@+
     * Constants defined for keys of array, makes typos less likely
     */
    const TITLE = 'title';
    const PERCENT = 'percent';
    const AMOUNT = 'amount';
    /**#@-*/

    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        return $this->getData(self::TITLE);
    }

    /**
     * {@inheritdoc}
     */
    public function getPercent()
    {
        return $this->getData(self::PERCENT);
    }

    /**
     * {@inheritdoc}
     */
    public function getAmount()
    {
        return $this->getData(self::AMOUNT);
    }

    /**
     * {@inheritdoc}
     */
    public function setTitle($title)
    {
        return $this->setData(self::TITLE, $title);
    }

    /**
     * {@inheritdoc}
     */
    public function setPercent($percent)
    {
        return $this->setData(self::PERCENT, $percent);
    }

    /**
     * {@inheritdoc}
     */
    public function setAmount($amount)
    {
        return $this->setData(self::AMOUNT, $amount);
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
        \Magento\Tax\Api\Data\GrandTotalRatesExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}

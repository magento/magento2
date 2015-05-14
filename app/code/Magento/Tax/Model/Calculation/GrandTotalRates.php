<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Model\Calculation;

use Magento\Tax\Api\Data\GrandTotalRatesInterface;
use Magento\Framework\Model\AbstractExtensibleModel;

/**
 * Grand Total Tax Details Model
 */
class GrandTotalRates extends AbstractExtensibleModel implements GrandTotalRatesInterface
{
    /**#@+
     * Constants defined for keys of array, makes typos less likely
     */
    const PERCENT = 'percent';
    const TITLE   = 'title';
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
    public function setTitle($title)
    {
        return $this->setData(self::TITLE, $title);
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
    public function setPercent($percent)
    {
        return $this->setData(self::PERCENT, $percent);
    }

    /**
     * {@inheritdoc}
     *
     * @return \Magento\Tax\Api\Data\GrandTotalRatesExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * {@inheritdoc}
     *
     * @param \Magento\Tax\Api\Data\GrandTotalRatesExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magento\Tax\Api\Data\GrandTotalRatesExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}

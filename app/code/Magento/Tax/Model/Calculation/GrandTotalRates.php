<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Model\Calculation;

use Magento\Tax\Api\Data\GrandTotalRatesInterface;
use Magento\Framework\Api\AbstractSimpleObject;

/**
 * Grand Total Tax Details Model
 */
class GrandTotalRates extends AbstractSimpleObject implements GrandTotalRatesInterface
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
        return $this->_get(self::TITLE);
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
        return $this->_get(self::PERCENT);
    }

    /**
     * {@inheritdoc}
     */
    public function setPercent($percent)
    {
        return $this->setData(self::PERCENT, $percent);
    }
}

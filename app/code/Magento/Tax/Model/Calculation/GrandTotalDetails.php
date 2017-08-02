<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Model\Calculation;

use Magento\Tax\Api\Data\GrandTotalDetailsInterface;
use Magento\Framework\Api\AbstractSimpleObject;

/**
 * Grand Total Tax Details Model
 * @since 2.0.0
 */
class GrandTotalDetails extends AbstractSimpleObject implements GrandTotalDetailsInterface
{
    /**#@+
     * Constants defined for keys of array, makes typos less likely
     */
    const AMOUNT = 'amount';
    const RATES = 'rates';
    const GROUP_ID = 'group_id';
    /**#@-*/

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getGroupId()
    {
        return $this->_get(self::GROUP_ID);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setGroupId($id)
    {
        return $this->setData(self::GROUP_ID, $id);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getAmount()
    {
        return $this->_get(self::AMOUNT);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setAmount($amount)
    {
        return $this->setData(self::AMOUNT, $amount);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getRates()
    {
        return $this->_get(self::RATES);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setRates($rates)
    {
        return $this->setData(self::RATES, $rates);
    }
}

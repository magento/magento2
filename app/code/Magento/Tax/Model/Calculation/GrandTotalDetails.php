<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Model\Calculation;

use Magento\Tax\Api\Data\GrandTotalDetailsInterface;
use Magento\Framework\Model\AbstractExtensibleModel;

/**
 * Grand Total Tax Details Model
 */
class GrandTotalDetails extends AbstractExtensibleModel implements GrandTotalDetailsInterface
{
    /**#@+
     * Constants defined for keys of array, makes typos less likely
     */
    const AMOUNT        = 'amount';
    const RATES         = 'rates';
    const GROUP_ID      = 'group_id';
    /**#@-*/

    /**
     * {@inheritdoc}
     */
    public function getGroupId()
    {
        return $this->getData(self::GROUP_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setGroupId($id)
    {
        return $this->setData(self::GROUP_ID, $id);
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
    public function setAmount($amount)
    {
        return $this->setData(self::AMOUNT, $amount);
    }

    /**
     * {@inheritdoc}
     */
    public function getRates()
    {
        return $this->getData(self::RATES);
    }

    /**
     * {@inheritdoc}
     */
    public function setRates($rates)
    {
        return $this->setData(self::RATES, $rates);
    }

    /**
     * {@inheritdoc}
     *
     * @return \Magento\Tax\Api\Data\GrandTotalDetailsExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * {@inheritdoc}
     *
     * @param \Magento\Tax\Api\Data\GrandTotalDetailsExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magento\Tax\Api\Data\GrandTotalDetailsExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}

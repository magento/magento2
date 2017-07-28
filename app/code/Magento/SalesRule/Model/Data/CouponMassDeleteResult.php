<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Model\Data;

/**
 * Class CouponMassDeleteResult
 *
 * @codeCoverageIgnore
 * @since 2.0.0
 */
class CouponMassDeleteResult extends \Magento\Framework\Api\AbstractSimpleObject implements
    \Magento\SalesRule\Api\Data\CouponMassDeleteResultInterface
{
    const FAILED_ITEMS = 'failed_items';
    const MISSING_ITEMS = 'missing_items';

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getFailedItems()
    {
        return $this->_get(self::FAILED_ITEMS);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setFailedItems(array $items)
    {
        return $this->setData(self::FAILED_ITEMS, $items);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getMissingItems()
    {
        return $this->_get(self::MISSING_ITEMS);
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function setMissingItems(array $items)
    {
        return $this->setData(self::MISSING_ITEMS, $items);
    }
}

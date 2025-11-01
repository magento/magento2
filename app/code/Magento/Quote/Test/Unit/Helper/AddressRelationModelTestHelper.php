<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Helper;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Helper class exposing address-related collection methods for Relation tests.
 */
class AddressRelationModelTestHelper extends AbstractModel
{
    public function __construct()
    {
        // Skip parent constructor
    }

    /** @return bool */
    public function itemsCollectionWasSet()
    {
        return false;
    }

    /** @return AbstractCollection|null */
    public function getItemsCollection()
    {
        return null;
    }

    /** @return bool */
    public function shippingRatesCollectionWasSet()
    {
        return false;
    }

    /** @return AbstractCollection|null */
    public function getShippingRatesCollection()
    {
        return null;
    }
}

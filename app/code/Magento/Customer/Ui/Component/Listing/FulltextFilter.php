<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Ui\Component\Listing;

use Magento\Customer\Model\ResourceModel\Grid\Collection as GridCollection;
use Magento\Framework\Api\Filter;
use Magento\Framework\Data\Collection;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\View\Element\UiComponent\DataProvider\FilterApplierInterface;

/**
 * Full text filter for customer listing data source
 */
class FulltextFilter implements FilterApplierInterface
{
    /**
     * @inheritdoc
     */
    public function apply(Collection $collection, Filter $filter)
    {
        if (!$collection instanceof AbstractDb) {
            throw new \InvalidArgumentException('Database collection required.');
        }

        /** @var GridCollection $gridCollection */
        $gridCollection = $collection;
        $value = $filter->getValue() !== null ? trim($filter->getValue()) : '';
        $gridCollection->addFullTextFilter($value);
    }
}

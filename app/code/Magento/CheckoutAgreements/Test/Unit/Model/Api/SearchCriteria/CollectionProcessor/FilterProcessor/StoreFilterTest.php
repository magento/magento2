<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CheckoutAgreements\Test\Unit\Model\Api\SearchCriteria\CollectionProcessor\FilterProcessor;

use Magento\Framework\Api\Filter;
use Magento\CheckoutAgreements\Model\ResourceModel\Agreement\Collection;
use PHPUnit\Framework\TestCase;
use Magento\CheckoutAgreements\Model\Api\SearchCriteria\CollectionProcessor\FilterProcessor\StoreFilter;

class StoreFilterTest extends TestCase
{
    /**
     * @var StoreFilter
     */
    private $model;

    protected function setUp(): void
    {
        $this->model = new StoreFilter();
    }

    public function testApply()
    {
        $filterMock = $this->createMock(Filter::class);
        $filterMock->expects($this->once())->method('getValue')->willReturn(1);
        $collectionMock = $this->createMock(
            Collection::class
        );
        $collectionMock->expects($this->once())->method('addStoreFilter')->with(1)->willReturnSelf();
        $this->assertTrue($this->model->apply($filterMock, $collectionMock));
    }
}

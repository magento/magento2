<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CheckoutAgreements\Test\Unit\Model\Api\SearchCriteria\CollectionProcessor\FilterProcessor;

use Magento\CheckoutAgreements\Model\Api\SearchCriteria\CollectionProcessor\FilterProcessor\StoreFilter;
use Magento\CheckoutAgreements\Model\ResourceModel\Agreement\Collection;
use Magento\Framework\Api\Filter;
use PHPUnit\Framework\TestCase;

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

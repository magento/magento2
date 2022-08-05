<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Review\Test\Unit\Block\Customer;

use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Framework\DataObject;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\View\Element\Template\Context;
use Magento\Review\Block\Customer\Recent;
use Magento\Review\Model\ResourceModel\Review\Product\Collection;
use Magento\Review\Model\ResourceModel\Review\Product\CollectionFactory;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RecentTest extends TestCase
{
    /** @var Recent */
    protected $object;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var Context|MockObject */
    protected $context;

    /** @var Collection|MockObject */
    protected $collection;

    /** @var MockObject */
    protected $collectionFactory;

    /** @var CurrentCustomer|MockObject */
    protected $currentCustomer;

    /** @var StoreManagerInterface|MockObject */
    protected $storeManager;

    protected function setUp(): void
    {
        $this->storeManager = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $this->context = $this->createMock(Context::class);
        $this->context->expects(
            $this->any()
        )->method(
            'getStoreManager'
        )->willReturn(
            $this->storeManager
        );
        $this->collection = $this->createMock(Collection::class);
        $this->collectionFactory = $this->createPartialMock(
            CollectionFactory::class,
            ['create']
        );
        $this->collectionFactory->expects(
            $this->once()
        )->method(
            'create'
        )->willReturn(
            $this->collection
        );
        $this->currentCustomer = $this->createMock(CurrentCustomer::class);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->object = $this->objectManagerHelper->getObject(
            Recent::class,
            [
                'context' => $this->context,
                'collectionFactory' => $this->collectionFactory,
                'currentCustomer' => $this->currentCustomer
            ]
        );
    }

    public function testGetCollection()
    {
        $this->storeManager->expects(
            $this->any()
        )->method(
            'getStore'
        )->willReturn(
            new DataObject(['id' => 42])
        );
        $this->currentCustomer->expects($this->any())->method('getCustomerId')->willReturn(4242);

        $this->collection->expects(
            $this->any()
        )->method(
            'addStoreFilter'
        )->with(
            42
        )->willReturn(
            $this->collection
        );
        $this->collection->expects(
            $this->any()
        )->method(
            'addCustomerFilter'
        )->with(
            4242
        )->willReturn(
            $this->collection
        );
        $this->collection->expects(
            $this->any()
        )->method(
            'setDateOrder'
        )->with()->willReturn(
            $this->collection
        );
        $this->collection->expects(
            $this->any()
        )->method(
            'setPageSize'
        )->with(
            5
        )->willReturn(
            $this->collection
        );
        $this->collection->expects($this->any())->method('load')->with()->willReturn($this->collection);
        $this->collection->expects(
            $this->any()
        )->method(
            'addReviewSummary'
        )->with()->willReturn(
            $this->collection
        );

        $this->assertSame($this->collection, $this->object->getReviews());
    }
}

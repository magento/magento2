<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Review\Test\Unit\Block\Customer;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class RecentTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Review\Block\Customer\Recent */
    protected $object;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var \Magento\Framework\View\Element\Template\Context|\PHPUnit\Framework\MockObject\MockObject */
    protected $context;

    /** @var \Magento\Review\Model\ResourceModel\Review\Product\Collection|\PHPUnit\Framework\MockObject\MockObject */
    protected $collection;

    /** @var \PHPUnit\Framework\MockObject\MockObject */
    protected $collectionFactory;

    /** @var \Magento\Customer\Helper\Session\CurrentCustomer|\PHPUnit\Framework\MockObject\MockObject */
    protected $currentCustomer;

    /** @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $storeManager;

    protected function setUp(): void
    {
        $this->storeManager = $this->createMock(\Magento\Store\Model\StoreManagerInterface::class);
        $this->context = $this->createMock(\Magento\Framework\View\Element\Template\Context::class);
        $this->context->expects(
            $this->any()
        )->method(
            'getStoreManager'
        )->willReturn(
            $this->storeManager
        );
        $this->collection = $this->createMock(\Magento\Review\Model\ResourceModel\Review\Product\Collection::class);
        $this->collectionFactory = $this->createPartialMock(
            \Magento\Review\Model\ResourceModel\Review\Product\CollectionFactory::class,
            ['create']
        );
        $this->collectionFactory->expects(
            $this->once()
        )->method(
            'create'
        )->willReturn(
            $this->collection
        );
        $this->currentCustomer = $this->createMock(\Magento\Customer\Helper\Session\CurrentCustomer::class);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->object = $this->objectManagerHelper->getObject(
            \Magento\Review\Block\Customer\Recent::class,
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
            new \Magento\Framework\DataObject(['id' => 42])
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

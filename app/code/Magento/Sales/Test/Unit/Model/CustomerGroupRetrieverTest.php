<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model;

use Magento\Backend\Model\Session\Quote;
use Magento\Customer\Api\Data\GroupInterface;
use Magento\Customer\Api\GroupManagementInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Model\CustomerGroupRetriever;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for class CustomerGroupRetriever.
 */
class CustomerGroupRetrieverTest extends TestCase
{
    /**
     * @var CustomerGroupRetriever
     */
    private $retriever;

    /**
     * @var Quote|MockObject
     */
    private $quoteSession;

    /**
     * @var GroupManagementInterface|MockObject
     */
    private $groupManagement;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->quoteSession = $this->getMockBuilder(Quote::class)
            ->disableOriginalConstructor()
            ->addMethods(['getQuoteId'])
            ->onlyMethods(['getQuote'])
            ->getMock();
        $this->groupManagement = $this->getMockBuilder(GroupManagementInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $helper = new ObjectManager($this);
        $this->retriever = $helper->getObject(
            CustomerGroupRetriever::class,
            [
                'quoteSession' => $this->quoteSession,
                'groupManagement' => $this->groupManagement
            ]
        );
    }

    /**
     * Test method getCustomerGroupId with quote session.
     */
    public function testGetCustomerGroupIdQuote()
    {
        $this->quoteSession->expects($this->atLeastOnce())->method('getQuoteId')->willReturn(1);
        $quote = $this->getMockBuilder(\Magento\Quote\Model\Quote::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->quoteSession->expects($this->atLeastOnce())->method('getQuote')->willReturn($quote);
        $quote->expects($this->once())->method('getCustomerGroupId')->willReturn(2);

        $this->assertEquals(2, $this->retriever->getCustomerGroupId());
    }

    /**
     * Test method getCustomerGroupId without quote session.
     */
    public function testGetCustomerGroupIdDefault()
    {
        $this->quoteSession->expects($this->atLeastOnce())->method('getQuoteId')->willReturn(0);
        $this->quoteSession->expects($this->never())->method('getQuote');
        $group = $this->getMockBuilder(GroupInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->groupManagement->expects($this->once())->method('getNotLoggedInGroup')->willReturn($group);
        $group->expects($this->once())->method('getId')->willReturn(2);

        $this->assertEquals(2, $this->retriever->getCustomerGroupId());
    }
}

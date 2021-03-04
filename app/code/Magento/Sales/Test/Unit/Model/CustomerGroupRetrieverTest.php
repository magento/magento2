<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Model;

use Magento\Backend\Model\Session\Quote;
use Magento\Customer\Api\GroupManagementInterface;

/**
 * Test for class CustomerGroupRetriever.
 */
class CustomerGroupRetrieverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Sales\Model\CustomerGroupRetriever
     */
    private $retriever;

    /**
     * @var Quote|\PHPUnit\Framework\MockObject\MockObject
     */
    private $quoteSession;

    /**
     * @var GroupManagementInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $groupManagement;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->quoteSession = $this->getMockBuilder(Quote::class)
            ->disableOriginalConstructor()
            ->setMethods(['getQuoteId', 'getQuote'])
            ->getMock();
        $this->groupManagement = $this->getMockBuilder(GroupManagementInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->retriever = $helper->getObject(
            \Magento\Sales\Model\CustomerGroupRetriever::class,
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
        $group = $this->getMockBuilder(\Magento\Customer\Api\Data\GroupInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->groupManagement->expects($this->once())->method('getNotLoggedInGroup')->willReturn($group);
        $group->expects($this->once())->method('getId')->willReturn(2);

        $this->assertEquals(2, $this->retriever->getCustomerGroupId());
    }
}

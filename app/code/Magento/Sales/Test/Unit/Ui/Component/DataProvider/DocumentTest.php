<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Unit\Ui\Component\DataProvider;

use Magento\Customer\Api\Data\GroupInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\Api\AttributeValue;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Ui\Component\DataProvider\Document;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Class DocumentTest
 */
class DocumentTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var GroupRepositoryInterface|MockObject
     */
    private $groupRepository;

    /**
     * @var AttributeValueFactory|MockObject
     */
    private $attributeValueFactory;

    /**
     * @var Document
     */
    private $document;

    protected function setUp()
    {
        $this->initAttributeValueFactoryMock();

        $this->groupRepository = $this->getMockForAbstractClass(GroupRepositoryInterface::class);

        $this->document = new Document($this->attributeValueFactory, $this->groupRepository);
    }

    /**
     * @covers \Magento\Sales\Ui\Component\DataProvider\Document::getCustomAttribute
     */
    public function testGetStateAttribute()
    {
        $this->document->setData('state', Invoice::STATE_PAID);

        $this->groupRepository->expects(static::never())
            ->method('getById');

        $attribute = $this->document->getCustomAttribute('state');
        static::assertEquals('Paid', $attribute->getValue());
    }

    /**
     * @covers \Magento\Sales\Ui\Component\DataProvider\Document::getCustomAttribute
     */
    public function testGetCustomerGroupAttribute()
    {
        $this->document->setData('customer_group_id', 1);

        $group = $this->getMockForAbstractClass(GroupInterface::class);

        $this->groupRepository->expects(static::once())
            ->method('getById')
            ->willReturn($group);

        $group->expects(static::once())
            ->method('getCode')
            ->willReturn('General');

        $attribute = $this->document->getCustomAttribute('customer_group_id');
        static::assertEquals('General', $attribute->getValue());
    }

    /**
     * Create mock for attribute value factory
     * @return void
     */
    private function initAttributeValueFactoryMock()
    {
        $this->attributeValueFactory = $this->getMockBuilder(AttributeValueFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $attributeValue = new AttributeValue();

        $this->attributeValueFactory->expects(static::once())
            ->method('create')
            ->willReturn($attributeValue);
    }
}

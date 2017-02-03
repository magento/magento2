<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SalesRule\Test\Unit\Model\ResourceModel;

class RuleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\SalesRule\Model\ResourceModel\Rule
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $ruleResource;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $adapter;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $select;

    public function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->ruleResource = $this->getMockBuilder('Magento\SalesRule\Model\ResourceModel\Rule')
            ->disableOriginalConstructor()
            ->getMock();

        $context = $this->getMockBuilder('Magento\Framework\Model\ResourceModel\Db\Context')
            ->disableOriginalConstructor()
            ->getMock();
        $connectionName = 'test';
        $resources = $this->getMockBuilder('Magento\Framework\App\ResourceConnection')
            ->disableOriginalConstructor()
            ->getMock();
        $context->expects($this->any())
            ->method('getResources')
            ->willReturn($resources);

        $this->adapter = $this->getMockBuilder('Magento\Framework\DB\Adapter\AdapterInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $resources->expects($this->any())
            ->method('getConnection')
            ->willReturn($this->adapter);
        $resources->expects($this->any())
            ->method('getTableName')
            ->withAnyParameters()
            ->willReturnArgument(0);

        $this->select = $this->getMockBuilder('Magento\Framework\DB\Select')
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = $objectManager->getObject(
            'Magento\SalesRule\Model\ResourceModel\Rule',
            [
                'context' => $context,
                'connectionName' => $connectionName
            ]
        );
    }

    public function testLoadCustomerGroupIds()
    {
        $customerGroupIds = [1];

        $object = $this->getMockBuilder('Magento\Framework\Model\AbstractModel')
            ->disableOriginalConstructor()
            ->getMock();
        $object->expects($this->once())
            ->method('getId')
            ->willReturn(1);
        $this->adapter->expects($this->once())
            ->method('select')
            ->willReturn($this->select);
        $this->select->expects($this->once())
            ->method('from')
            ->with('salesrule_customer_group', ['customer_group_id'])
            ->willReturnSelf();
        $this->select->expects($this->once())
            ->method('where')
            ->with('rule_id = ?', 1)
            ->willReturnSelf();
        $this->adapter->expects($this->once())
            ->method('fetchCol')
            ->with($this->select)
            ->willReturn($customerGroupIds);

        $object->expects($this->once())
            ->method('setData')
            ->with('customer_group_ids', $customerGroupIds);

        $this->model->loadCustomerGroupIds($object);
    }

    public function testLoadWebsiteIds()
    {
        $websiteIds = [1];

        $object = $this->getMockBuilder('Magento\Framework\Model\AbstractModel')
            ->disableOriginalConstructor()
            ->getMock();
        $object->expects($this->once())
            ->method('getId')
            ->willReturn(1);
        $this->adapter->expects($this->once())
            ->method('select')
            ->willReturn($this->select);
        $this->select->expects($this->once())
            ->method('from')
            ->with('salesrule_website', ['website_id'])
            ->willReturnSelf();
        $this->select->expects($this->once())
            ->method('where')
            ->with('rule_id = ?', 1)
            ->willReturnSelf();
        $this->adapter->expects($this->once())
            ->method('fetchCol')
            ->with($this->select)
            ->willReturn($websiteIds);

        $object->expects($this->once())
            ->method('setData')
            ->with('website_ids', $websiteIds);

        $this->model->loadWebsiteIds($object);
    }
}

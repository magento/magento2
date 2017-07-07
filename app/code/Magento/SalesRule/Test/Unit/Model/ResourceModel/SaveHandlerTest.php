<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Test\Unit\Model\ResourceModel;

use Magento\SalesRule\Model\ResourceModel\SaveHandler;
use Magento\SalesRule\Model\ResourceModel\Rule;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\SalesRule\Api\Data\RuleInterface;

/**
 * Class SaveHandlerTest
 */
class SaveHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SaveHandler
     */
    protected $model;

    /**
     * @var Rule|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $ruleResource;

    /**
     * @var MetadataPool|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $metadataPool;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * Setup the test
     */
    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $className = \Magento\SalesRule\Model\ResourceModel\Rule::class;
        $this->ruleResource = $this->getMock($className, [], [], '', false);

        $className = \Magento\Framework\EntityManager\MetadataPool::class;
        $this->metadataPool = $this->getMock($className, [], [], '', false);

        $this->model = $this->objectManager->getObject(
            \Magento\SalesRule\Model\ResourceModel\SaveHandler::class,
            [
                'ruleResource' => $this->ruleResource,
                'metadataPool' => $this->metadataPool,
            ]
        );
    }

    /**
     * test Execute
     */
    public function testExecuteNoData()
    {
        $entityData = [
            'row_id' => 2,
            'rule_id' => 1
        ];

        $className = \Magento\Framework\EntityManager\EntityMetadata::class;
        $metadata = $this->getMock($className, [], [], '', false);

        $metadata->expects($this->once())
            ->method('getLinkField')
            ->willReturn('rule_id');

        $this->metadataPool->expects($this->once())
            ->method('getMetadata')
            ->willReturn($metadata);

        $result = $this->model->execute(RuleInterface::class, $entityData);
        $this->assertEquals($entityData, $result);
    }

    public function testExecute()
    {
        $customers = [1, 2];
        $websites = [3, 4, 5];

        $entityData = [
            'row_id' => 2,
            'rule_id' => 1,
            'website_ids' => $websites,
            'customer_group_ids' => $customers
        ];

        $className = \Magento\Framework\EntityManager\EntityMetadata::class;
        $metadata = $this->getMock($className, [], [], '', false);

        $metadata->expects($this->once())
            ->method('getLinkField')
            ->willReturn('rule_id');

        $this->metadataPool->expects($this->once())
            ->method('getMetadata')
            ->willReturn($metadata);

        $this->ruleResource->expects($this->exactly(2))
            ->method('bindRuleToEntity');

        $result = $this->model->execute(RuleInterface::class, $entityData);
        $this->assertEquals($entityData, $result);
    }

    public function testExecuteWithString()
    {
        $customers = '1,2';
        $websites = '3,4,5';

        $entityData = [
            'row_id' => 1,
            'rule_id' => 1,
            'website_ids' => $websites,
            'customer_group_ids' => $customers
        ];

        $className = \Magento\Framework\EntityManager\EntityMetadata::class;
        $metadata = $this->getMock($className, [], [], '', false);

        $metadata->expects($this->once())
            ->method('getLinkField')
            ->willReturn('rule_id');

        $this->metadataPool->expects($this->once())
            ->method('getMetadata')
            ->willReturn($metadata);

        $this->ruleResource->expects($this->any())
            ->method('bindRuleToEntity')
            ->withConsecutive([1, [3, 4, 5]], [1, [1, 2]]);

        $result = $this->model->execute(RuleInterface::class, $entityData);
        $this->assertEquals($entityData, $result);
    }
}

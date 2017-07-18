<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Test\Unit\Model\ResourceModel;

use Magento\SalesRule\Model\ResourceModel\ReadHandler;
use Magento\SalesRule\Model\ResourceModel\Rule;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\SalesRule\Api\Data\RuleInterface;

/**
 * Class ReadHandlerTest
 */
class ReadHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\SalesRule\Model\ResourceModel\ReadHandler
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
            \Magento\SalesRule\Model\ResourceModel\ReadHandler::class,
            [
                'ruleResource' => $this->ruleResource,
                'metadataPool' => $this->metadataPool,
            ]
        );
    }

    /**
     * test Execute
     */
    public function testExecute()
    {
        $entityData = [
            'row_id' => 2,
            'rule_id' => 1
        ];

        $customers = [1, 2];
        $websites = [3, 4, 5];

        $className = \Magento\Framework\EntityManager\EntityMetadata::class;
        $metadata = $this->getMock($className, [], [], '', false);

        $metadata->expects($this->once())
            ->method('getLinkField')
            ->willReturn('rule_id');

        $this->metadataPool->expects($this->once())
            ->method('getMetadata')
            ->willReturn($metadata);

        $this->ruleResource->expects($this->once())
            ->method('getCustomerGroupIds')
            ->willReturn($customers);

        $this->ruleResource->expects($this->once())
            ->method('getWebsiteIds')
            ->willReturn($websites);

        $result = $this->model->execute(RuleInterface::class, $entityData);
        $expected = [
            'row_id' => 2,
            'rule_id' => 1,
            'customer_group_ids' => [1, 2],
            'website_ids' => [3, 4, 5],
        ];

        $this->assertEquals($expected, $result);
    }
}

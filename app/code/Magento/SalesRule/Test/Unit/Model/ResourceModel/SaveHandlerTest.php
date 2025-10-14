<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Test\Unit\Model\ResourceModel;

use Magento\Framework\EntityManager\EntityMetadata;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\SalesRule\Api\Data\RuleInterface;
use Magento\SalesRule\Model\ResourceModel\Rule;
use Magento\SalesRule\Model\ResourceModel\SaveHandler;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SaveHandlerTest extends TestCase
{
    /**
     * @var SaveHandler
     */
    protected $model;

    /**
     * @var Rule|MockObject
     */
    protected $ruleResource;

    /**
     * @var MetadataPool|MockObject
     */
    protected $metadataPool;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * Setup the test
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        $className = Rule::class;
        $this->ruleResource = $this->createMock($className);

        $className = MetadataPool::class;
        $this->metadataPool = $this->createMock($className);

        $this->model = $this->objectManager->getObject(
            SaveHandler::class,
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

        $className = EntityMetadata::class;
        $metadata = $this->createMock($className);

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

        $className = EntityMetadata::class;
        $metadata = $this->createMock($className);

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

        $className = EntityMetadata::class;
        $metadata = $this->createMock($className);

        $metadata->expects($this->once())
            ->method('getLinkField')
            ->willReturn('rule_id');

        $this->metadataPool->expects($this->once())
            ->method('getMetadata')
            ->willReturn($metadata);

        $this->ruleResource->expects($this->any())
            ->method('bindRuleToEntity')
            ->willReturnCallback(function ($arg1, $arg2) {
                if ($arg1 == 1 && is_array($arg2)) {
                    return null;
                }
            });

        $result = $this->model->execute(RuleInterface::class, $entityData);
        $this->assertEquals($entityData, $result);
    }
}

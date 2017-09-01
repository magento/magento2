<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\EntityManager\Test\Unit;

use PHPUnit\Framework\TestCase;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\EntityManager\OperationPool;

class OperationPoolTest extends TestCase
{
    public function testGetOperationUsesDefaultValueForEntityThatDoesNotProvideCustomMapping()
    {
        $objectManagerMock = $this->createMock(ObjectManagerInterface::class);
        $operationPool = new OperationPool(
            $objectManagerMock,
            []
        );

        $objectManagerMock->expects($this->once())
            ->method('get')
            ->with(\Magento\Framework\EntityManager\Operation\Read::class);
        $operationPool->getOperation('entity_type', 'read');
    }

    public function testGetOperationUsesOverriddenDefaultValueForEntityThatDoesNotProvideCustomMapping()
    {
        $customReadOperation = 'CustomReadOperation';
        $objectManagerMock = $this->createMock(ObjectManagerInterface::class);
        $operationPool = new OperationPool(
            $objectManagerMock,
            [
                'default' => [
                    'read' => $customReadOperation,
                    'new' => 'CustomNewOperation',
                ],
            ]
        );

        $objectManagerMock->expects($this->once())
            ->method('get')
            ->with($customReadOperation);
        $operationPool->getOperation('entity_type', 'read');
    }
}

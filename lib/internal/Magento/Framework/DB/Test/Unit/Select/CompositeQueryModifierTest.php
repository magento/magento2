<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\DB\Test\Unit\Select;

use Magento\Framework\DB\Select;
use Magento\Framework\DB\Select\CompositeQueryModifier;
use Magento\Framework\DB\Select\QueryModifierInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

class CompositeQueryModifierTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
    }

    public function testModify()
    {
        $queryModifierMockOne = $this->getMockForAbstractClass(QueryModifierInterface::class);
        $queryModifierMockTwo = $this->getMockForAbstractClass(QueryModifierInterface::class);
        $selectMock = $this->createMock(Select::class);
        $queryModifierMockOne->expects($this->once())
            ->method('modify')
            ->with($selectMock);
        $queryModifierMockTwo->expects($this->once())
            ->method('modify')
            ->with($selectMock);
        $compositeQueryModifier = $this->objectManager->getObject(
            CompositeQueryModifier::class,
            [
                'queryModifiers' => [
                    $queryModifierMockOne,
                    $queryModifierMockTwo
                ]
            ]
        );
        $compositeQueryModifier->modify($selectMock);
    }
}

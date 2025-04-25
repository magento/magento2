<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\View\Test\Unit\Layout\Data;

use Magento\Framework\App\State;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\View\Layout\Data\Structure;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Rule\InvokedCount;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class StructureTest extends TestCase
{
    /**
     * @var LoggerInterface|MockObject
     */
    protected $loggerMock;

    /**
     * @var State|MockObject
     */
    protected $stateMock;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var Structure
     */
    protected $dataStructure;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->loggerMock = $this->getMockForAbstractClass(LoggerInterface::class);
        $this->stateMock = $this->createMock(State::class);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->dataStructure = $this->objectManagerHelper->getObject(
            Structure::class,
            [
                'logger' => $this->loggerMock,
                'state' => $this->stateMock
            ]
        );
    }

    /**
     * @param InvokedCount $loggerExpects
     * @param string $stateMode
     * @return void
     * @dataProvider reorderChildElementLogDataProvider
     */
    public function testReorderChildElementLog($loggerExpects, $stateMode)
    {
        $parentName = 'parent';
        $childName = 'child';
        $offsetOrSibling = '-';

        $this->stateMock->expects($this->once())
            ->method('getMode')
            ->willReturn($stateMode);
        $this->loggerMock->expects($loggerExpects)
            ->method('info')
            ->with(
                "Broken reference: the '{$childName}' tries to reorder itself towards '', but " .
                "their parents are different: '{$parentName}' and '' respectively."
            );

        $this->dataStructure->reorderChildElement($parentName, $childName, $offsetOrSibling);
    }

    /**
     * @return array
     */
    public static function reorderChildElementLogDataProvider()
    {
        return [
            [
                'loggerExpects' => self::once(),
                'stateMode' => State::MODE_DEVELOPER
            ],
            [
                'loggerExpects' => self::never(),
                'stateMode' => State::MODE_DEFAULT
            ],
            [
                'loggerExpects' => self::never(),
                'stateMode' => State::MODE_PRODUCTION
            ]
        ];
    }
}

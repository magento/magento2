<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\View\Test\Unit\Layout\Data;

use Magento\Framework\App\State;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\View\Layout\Data\Structure;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Rule\InvokedCount;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Psr\Log\LoggerInterface;

class StructureTest extends TestCase
{
    use MockCreationTrait;
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
        $this->loggerMock = $this->createMock(LoggerInterface::class);
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
     * @return void     */
    #[DataProvider('reorderChildElementLogDataProvider')]
    public function testReorderChildElementLog($loggerExpects, $stateMode)
    {
        $parentName = 'parent';
        $childName = 'child';
        $offsetOrSibling = '-';

        $this->stateMock->expects($this->once())
            ->method('getMode')
            ->willReturn($stateMode);
        $expects = is_string($loggerExpects) 
            ? $this->createInvocationMatcher($loggerExpects) 
            : $loggerExpects;
        $this->loggerMock->expects($expects)
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
                'loggerExpects' => 'once',
                'stateMode' => State::MODE_DEVELOPER
            ],
            [
                'loggerExpects' => 'never',
                'stateMode' => State::MODE_DEFAULT
            ],
            [
                'loggerExpects' => 'never',
                'stateMode' => State::MODE_PRODUCTION
            ]
        ];
    }
}

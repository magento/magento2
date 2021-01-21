<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Test\Unit\Layout\Data;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\App\State;

class StructureTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $loggerMock;

    /**
     * @var State|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $stateMock;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var \Magento\Framework\View\Layout\Data\Structure
     */
    protected $dataStructure;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->loggerMock = $this->createMock(\Psr\Log\LoggerInterface::class);
        $this->stateMock = $this->createMock(\Magento\Framework\App\State::class);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->dataStructure = $this->objectManagerHelper->getObject(
            \Magento\Framework\View\Layout\Data\Structure::class,
            [
                'logger' => $this->loggerMock,
                'state' => $this->stateMock
            ]
        );
    }

    /**
     * @param \PHPUnit\Framework\MockObject\Matcher\InvokedCount $loggerExpects
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
    public function reorderChildElementLogDataProvider()
    {
        return [
            [
                'loggerExpects' => $this->once(),
                'stateMode' => State::MODE_DEVELOPER
            ],
            [
                'loggerExpects' => $this->never(),
                'stateMode' => State::MODE_DEFAULT
            ],
            [
                'loggerExpects' => $this->never(),
                'stateMode' => State::MODE_PRODUCTION
            ]
        ];
    }
}

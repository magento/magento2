<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Test\Unit\Layout\Data;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\App\State;

class StructureTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $loggerMock;

    /**
     * @var State|\PHPUnit_Framework_MockObject_MockObject
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
    protected function setUp()
    {
        $this->loggerMock = $this->getMock(\Psr\Log\LoggerInterface::class);
        $this->stateMock = $this->getMock(\Magento\Framework\App\State::class, [], [], '', false);

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
     * @param \PHPUnit_Framework_MockObject_Matcher_InvokedCount $loggerExpects
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

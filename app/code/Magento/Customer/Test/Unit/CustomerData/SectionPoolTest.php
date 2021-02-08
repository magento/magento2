<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Unit\CustomerData;

use Magento\Customer\CustomerData\SectionPool;

class SectionPoolTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $objectManagerMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $identifierMock;

    /**
     * @var array|null
     */
    protected $sectionSourceMap;

    /**
     * @var SectionPool
     */
    protected $model;

    protected function setUp(): void
    {
        $this->objectManagerMock = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);
        $this->identifierMock = $this->createMock(\Magento\Customer\CustomerData\Section\Identifier::class);
        $this->sectionSourceMap = ['section1' => 'b'];
        $this->model = new SectionPool(
            $this->objectManagerMock,
            $this->identifierMock,
            $this->sectionSourceMap
        );
    }

    public function testGetSectionsDataAllSections()
    {
        $sectionNames = ['section1'];
        $sectionsData = ['data1', 'data2'];
        $allSectionsData = [
            'section1' => [
                'data1',
                'data2'
            ]
        ];
        $identifierResult = [1, 2, 3];

        $sectionSourceMock = $this->createMock(\Magento\Customer\CustomerData\SectionSourceInterface::class);
        $this->objectManagerMock->expects($this->once())
            ->method('get')
            ->with('b')
            ->willReturn($sectionSourceMock);
        $sectionSourceMock->expects($this->once())->method('getSectionData')->willReturn($sectionsData);

        $this->identifierMock->expects($this->once())
            ->method('markSections')
            //check also default value for $forceTimestamp = false
            ->with($allSectionsData, $sectionNames, false)
            ->willReturn($identifierResult);
        $modelResult = $this->model->getSectionsData($sectionNames);
        $this->assertEquals($identifierResult, $modelResult);
    }

    /**
     */
    public function testGetSectionsDataAllSectionsException()
    {
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);
        $this->expectExceptionMessage('b doesn\'t extend \\Magento\\Customer\\CustomerData\\SectionSourceInterface');

        $sectionNames = [];
        $identifierResult = [1, 2, 3];
        $this->objectManagerMock->expects($this->once())
            ->method('get')
            ->with('b')
            ->willReturn($this->model);
        $modelResult = $this->model->getSectionsData($sectionNames);
        $this->assertEquals($identifierResult, $modelResult);
    }
}

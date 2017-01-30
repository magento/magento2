<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Unit\CustomerData;

use Magento\Customer\CustomerData\SectionPool;

class SectionPoolTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
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

    protected function setUp()
    {
        $this->objectManagerMock = $this->getMock('\Magento\Framework\ObjectManagerInterface', [], [], '', false);
        $this->identifierMock = $this->getMock('\Magento\Customer\CustomerData\Section\Identifier', [], [], '', false);
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

        $sectionSourceMock = $this->getMock('\Magento\Customer\CustomerData\SectionSourceInterface', [], [], '', false);
        $this->objectManagerMock->expects($this->once())
            ->method('get')
            ->with('b')
            ->willReturn($sectionSourceMock);
        $sectionSourceMock->expects($this->once())->method('getSectionData')->willReturn($sectionsData);

        $this->identifierMock->expects($this->once())
            ->method('markSections')
            //check also default value for $updateIds = false
            ->with($allSectionsData, $sectionNames, false)
            ->willReturn($identifierResult);
        $modelResult = $this->model->getSectionsData($sectionNames);
        $this->assertEquals($identifierResult, $modelResult);
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage b doesn't extend \Magento\Customer\CustomerData\SectionSourceInterface
     */
    public function testGetSectionsDataAllSectionsException()
    {
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

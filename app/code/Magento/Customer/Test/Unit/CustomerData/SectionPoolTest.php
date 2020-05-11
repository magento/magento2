<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\CustomerData;

use Magento\Customer\CustomerData\Section\Identifier;
use Magento\Customer\CustomerData\SectionPool;
use Magento\Customer\CustomerData\SectionSourceInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SectionPoolTest extends TestCase
{
    /**
     * @var MockObject
     */
    protected $objectManagerMock;

    /**
     * @var MockObject
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
        $this->objectManagerMock = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        $this->identifierMock = $this->createMock(Identifier::class);
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

        $sectionSourceMock = $this->getMockForAbstractClass(SectionSourceInterface::class);
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

    public function testGetSectionsDataAllSectionsException()
    {
        $this->expectException(LocalizedException::class);
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

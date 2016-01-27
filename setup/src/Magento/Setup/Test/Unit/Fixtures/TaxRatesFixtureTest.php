<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Fixtures;

use \Magento\Setup\Fixtures\TaxRatesFixture;

class TaxRatesFixtureTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Setup\Fixtures\FixtureModel
     */
    private $fixtureModelMock;

    /**
     * @var \Magento\Setup\Fixtures\TaxRatesFixture
     */
    private $model;

    public function setUp()
    {
        $this->fixtureModelMock = $this->getMock('\Magento\Setup\Fixtures\FixtureModel', [], [], '', false);

        $this->model = new TaxRatesFixture($this->fixtureModelMock);
    }

    public function testExecute()
    {
        $rateMock = $this->getMock('Magento\Tax\Model\Calculation\Rate', ['setId', 'delete'], [], '', false);

        $collectionMock =
            $this->getMock('Magento\Tax\Model\ResourceModel\Calculation\Rate\Collection', [], [], '', false);
        $collectionMock->expects($this->once())
            ->method('getAllIds')
            ->willReturn([1]);

        $csvImportHandlerMock = $this->getMock(
            'Magento\TaxImportExport\Model\Rate\CsvImportHandler',
            [],
            [],
            '',
            false
        );

        $valueMap = [
            ['Magento\Tax\Model\Calculation\Rate', $rateMock],
            ['Magento\Tax\Model\ResourceModel\Calculation\Rate\Collection', $collectionMock]
        ];

        $objectManagerMock = $this->getMock('Magento\Framework\ObjectManager\ObjectManager', [], [], '', false);
        $objectManagerMock->expects($this->exactly(2))
            ->method('get')
            ->will($this->returnValueMap($valueMap));
        $objectManagerMock->expects($this->once())
            ->method('create')
            ->willReturn($csvImportHandlerMock);

        $this->fixtureModelMock
            ->expects($this->once())
            ->method('getValue')
            ->willReturn('taxRates.file');
        $this->fixtureModelMock
            ->expects($this->exactly(3))
            ->method('getObjectManager')
            ->willReturn($objectManagerMock);

        $this->model->execute();
    }

    public function testNoFixtureConfigValue()
    {
        $csvImportHandlerMock = $this->getMock(
            'Magento\TaxImportExport\Model\Rate\CsvImportHandler',
            [],
            [],
            '',
            false
        );
        $csvImportHandlerMock->expects($this->never())->method('importFromCsvFile');

        $objectManagerMock = $this->getMock('Magento\Framework\ObjectManager\ObjectManager', [], [], '', false);
        $objectManagerMock->expects($this->never())
            ->method('create')
            ->willReturn($csvImportHandlerMock);

        $this->fixtureModelMock
            ->expects($this->never())
            ->method('getObjectManager')
            ->willReturn($objectManagerMock);
        $this->fixtureModelMock
            ->expects($this->once())
            ->method('getValue')
            ->willReturn(false);

        $this->model->execute();
    }

    public function testGetActionTitle()
    {
        $this->assertSame('Generating tax rates', $this->model->getActionTitle());
    }

    public function testIntroduceParamLabels()
    {
        $this->assertSame([], $this->model->introduceParamLabels());
    }
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Fixtures;

use Magento\Framework\ObjectManager\ObjectManager;
use Magento\Setup\Fixtures\FixtureModel;
use Magento\Setup\Fixtures\TaxRatesFixture;
use Magento\Tax\Model\Calculation\Rate;
use Magento\Tax\Model\ResourceModel\Calculation\Rate\Collection;
use Magento\TaxImportExport\Model\Rate\CsvImportHandler;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TaxRatesFixtureTest extends TestCase
{

    /**
     * @var MockObject|FixtureModel
     */
    private $fixtureModelMock;

    /**
     * @var TaxRatesFixture
     */
    private $model;

    protected function setUp(): void
    {
        $this->fixtureModelMock = $this->createMock(FixtureModel::class);

        $this->model = new TaxRatesFixture($this->fixtureModelMock);
    }

    public function testExecute()
    {
        $rateMock = $this->createPartialMock(Rate::class, ['setId', 'delete']);

        $collectionMock =
            $this->createMock(Collection::class);
        $collectionMock->expects($this->once())
            ->method('getAllIds')
            ->willReturn([1]);

        $csvImportHandlerMock = $this->createMock(CsvImportHandler::class);

        $valueMap = [
            [Rate::class, $rateMock],
            [Collection::class, $collectionMock]
        ];

        $objectManagerMock = $this->createMock(ObjectManager::class);
        $objectManagerMock->expects($this->exactly(2))
            ->method('get')
            ->willReturnMap($valueMap);
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
        $csvImportHandlerMock = $this->createMock(CsvImportHandler::class);
        $csvImportHandlerMock->expects($this->never())->method('importFromCsvFile');

        $objectManagerMock = $this->createMock(ObjectManager::class);
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

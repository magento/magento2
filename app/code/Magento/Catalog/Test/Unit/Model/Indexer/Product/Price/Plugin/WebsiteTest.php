<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Indexer\Product\Price\Plugin;

use Magento\Catalog\Model\Indexer\Product\Price\DimensionModeConfiguration;
use Magento\Catalog\Model\Indexer\Product\Price\Plugin\Website;
use Magento\Catalog\Model\Indexer\Product\Price\TableMaintainer;
use Magento\Framework\Indexer\Dimension;
use Magento\Framework\Indexer\DimensionFactory;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\Indexer\WebsiteDimensionProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class WebsiteTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var Website
     */
    protected $model;

    /**
     * @var DimensionFactory|MockObject
     */
    protected $dimensionFactory;

    /**
     * @var TableMaintainer|MockObject
     */
    protected $tableMaintainer;

    /**
     * @var DimensionModeConfiguration|MockObject
     */
    protected $dimensionModeConfiguration;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        $this->dimensionFactory = $this->createPartialMock(
            DimensionFactory::class,
            ['create']
        );

        $this->tableMaintainer = $this->createPartialMock(
            TableMaintainer::class,
            ['dropTablesForDimensions', 'createTablesForDimensions']
        );

        $this->dimensionModeConfiguration = $this->createPartialMock(
            DimensionModeConfiguration::class,
            ['getDimensionConfiguration']
        );

        $this->model = $this->objectManager->getObject(
            Website::class,
            [
                'dimensionFactory' => $this->dimensionFactory,
                'tableMaintainer' => $this->tableMaintainer,
                'dimensionModeConfiguration' => $this->dimensionModeConfiguration,
            ]
        );
    }

    public function testAfterDelete()
    {
        $dimensionMock = $this->createMock(Dimension::class);

        $this->dimensionFactory->expects($this->once())->method('create')->willReturn(
            $dimensionMock
        );
        $this->tableMaintainer->expects($this->once())->method('dropTablesForDimensions')->with(
            [$dimensionMock]
        );

        $this->dimensionModeConfiguration->expects($this->once())->method('getDimensionConfiguration')->willReturn(
            [WebsiteDimensionProvider::DIMENSION_NAME]
        );

        $subjectMock = $this->createMock(AbstractDb::class);
        $objectResourceMock = $this->createMock(AbstractDb::class);
        $websiteMock = $this->createMock(AbstractModel::class);
        $websiteMock->expects($this->once())
            ->method('getId')
            ->willReturn(1);

        $this->assertEquals(
            $objectResourceMock,
            $this->model->afterDelete($subjectMock, $objectResourceMock, $websiteMock)
        );
    }

    public function testAfterDeleteOnModeWithoutWebsiteDimension()
    {
        $dimensionMock = $this->createMock(Dimension::class);

        $this->dimensionFactory->expects($this->never())->method('create')->willReturn(
            $dimensionMock
        );
        $this->tableMaintainer->expects($this->never())->method('dropTablesForDimensions')->with(
            [$dimensionMock]
        );

        $this->dimensionModeConfiguration->expects($this->once())->method('getDimensionConfiguration')->willReturn(
            []
        );

        $subjectMock = $this->createMock(AbstractDb::class);
        $objectResourceMock = $this->createMock(AbstractDb::class);
        $websiteMock = $this->createMock(AbstractModel::class);
        $websiteMock->expects($this->once())
            ->method('getId')
            ->willReturn(1);

        $this->assertEquals(
            $objectResourceMock,
            $this->model->afterDelete($subjectMock, $objectResourceMock, $websiteMock)
        );
    }

    public function testAfterSave()
    {
        $dimensionMock = $this->createMock(Dimension::class);

        $this->dimensionFactory->expects($this->once())->method('create')->willReturn(
            $dimensionMock
        );
        $this->tableMaintainer->expects($this->once())->method('createTablesForDimensions')->with(
            [$dimensionMock]
        );

        $this->dimensionModeConfiguration->expects($this->once())->method('getDimensionConfiguration')->willReturn(
            [WebsiteDimensionProvider::DIMENSION_NAME]
        );

        $subjectMock = $this->createMock(AbstractDb::class);
        $objectResourceMock = $this->createMock(AbstractDb::class);
        $websiteMock = $this->createMock(AbstractModel::class);
        $websiteMock->expects($this->once())
            ->method('getId')
            ->willReturn(1);
        $websiteMock->expects($this->once())
            ->method('isObjectNew')
            ->willReturn(true);

        $this->assertEquals(
            $objectResourceMock,
            $this->model->afterSave($subjectMock, $objectResourceMock, $websiteMock)
        );
    }

    public function testAfterSaveOnModeWithoutWebsiteDimension()
    {
        $dimensionMock = $this->createMock(Dimension::class);

        $this->dimensionFactory->expects($this->never())->method('create')->willReturn(
            $dimensionMock
        );
        $this->tableMaintainer->expects($this->never())->method('createTablesForDimensions')->with(
            [$dimensionMock]
        );

        $this->dimensionModeConfiguration->expects($this->once())->method('getDimensionConfiguration')->willReturn(
            []
        );

        $subjectMock = $this->createMock(AbstractDb::class);
        $objectResourceMock = $this->createMock(AbstractDb::class);
        $websiteMock = $this->createMock(AbstractModel::class);
        $websiteMock->expects($this->once())
            ->method('getId')
            ->willReturn(1);
        $websiteMock->expects($this->once())
            ->method('isObjectNew')
            ->willReturn(true);

        $this->assertEquals(
            $objectResourceMock,
            $this->model->afterSave($subjectMock, $objectResourceMock, $websiteMock)
        );
    }
}

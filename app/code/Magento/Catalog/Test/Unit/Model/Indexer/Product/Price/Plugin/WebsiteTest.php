<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\Indexer\Product\Price\Plugin;

class WebsiteTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Price\Plugin\Website
     */
    protected $model;

    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Price\Processor|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\Indexer\DimensionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $dimensionFactory;

    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Price\TableMaintainer|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $tableMaintainer;

    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->scopeConfig = $this->createPartialMock(
            \Magento\Framework\App\Config\ScopeConfigInterface::class,
            ['getValue', 'isSetFlag']
        );

        $this->dimensionFactory = $this->createPartialMock(
            \Magento\Framework\Indexer\DimensionFactory::class,
            ['create']
        );

        $this->tableMaintainer = $this->createPartialMock(
            \Magento\Catalog\Model\Indexer\Product\Price\TableMaintainer::class,
            ['dropTablesForDimensions', 'createTablesForDimensions']
        );

        $this->model = $this->objectManager->getObject(
            \Magento\Catalog\Model\Indexer\Product\Price\Plugin\Website::class,
            [
                'configReader' => $this->scopeConfig,
                'dimensionFactory' => $this->dimensionFactory,
                'tableMaintainer' => $this->tableMaintainer,
            ]
        );
    }

    public function testAfterDelete()
    {
        $dimensionMock = $this->createMock(\Magento\Framework\Indexer\Dimension::class);

        $this->scopeConfig->expects($this->once())->method('getValue')->willReturn(
            \Magento\Catalog\Model\Indexer\Product\Price\ModeSwitcher::INPUT_KEY_WEBSITE
        );
        $this->dimensionFactory->expects($this->once())->method('create')->willReturn(
            $dimensionMock
        );
        $this->tableMaintainer->expects($this->once())->method('dropTablesForDimensions')->with(
            [$dimensionMock]
        );

        $subjectMock = $this->createMock(\Magento\Framework\Model\ResourceModel\Db\AbstractDb::class);
        $objectResourceMock = $this->createMock(\Magento\Framework\Model\ResourceModel\Db\AbstractDb::class);
        $websiteMock = $this->createMock(\Magento\Framework\Model\AbstractModel::class);
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
        $dimensionMock = $this->createMock(\Magento\Framework\Indexer\Dimension::class);

        $this->scopeConfig->expects($this->once())->method('getValue')->willReturn(
            \Magento\Catalog\Model\Indexer\Product\Price\ModeSwitcher::INPUT_KEY_WEBSITE
        );
        $this->dimensionFactory->expects($this->once())->method('create')->willReturn(
            $dimensionMock
        );
        $this->tableMaintainer->expects($this->once())->method('createTablesForDimensions')->with(
            [$dimensionMock]
        );

        $subjectMock = $this->createMock(\Magento\Framework\Model\ResourceModel\Db\AbstractDb::class);
        $objectResourceMock = $this->createMock(\Magento\Framework\Model\ResourceModel\Db\AbstractDb::class);
        $websiteMock = $this->createMock(\Magento\Framework\Model\AbstractModel::class);
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

<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Test\Unit\Model\Config;

use Magento\Framework\App\CacheInterface;
use Magento\Store\Model\Config\Importer;
use Magento\Store\Model\ResourceModel\Website;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManager;
use Magento\Framework\DB\Adapter\AdapterInterface;
use PHPUnit_Framework_MockObject_MockObject as Mock;

/**
 * @inheritdoc
 */
class ImporterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Importer
     */
    private $model;

    /**
     * @var Importer\DataDifferenceCalculator|Mock
     */
    private $dataDifferenceCalculatorMock;

    /**
     * @var Importer\Processor\ProcessorFactory|Mock
     */
    private $processorFactoryMock;

    /**
     * @var Importer\Processor\ProcessorInterface|Mock
     */
    private $processorMock;

    /**
     * @var StoreManager|Mock
     */
    private $storeManagerMock;

    /**
     * @var CacheInterface|Mock
     */
    private $cacheManagerMock;

    /**
     * @var Website|Mock
     */
    private $resourceMock;

    /**
     * @var AdapterInterface|Mock
     */
    private $connectionMock;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->dataDifferenceCalculatorMock = $this->getMockBuilder(Importer\DataDifferenceCalculator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->processorFactoryMock = $this->getMockBuilder(Importer\Processor\ProcessorFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->processorMock = $this->getMockBuilder(Importer\Processor\ProcessorInterface::class)
            ->getMockForAbstractClass();
        $this->storeManagerMock = $this->getMockBuilder(StoreManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->cacheManagerMock = $this->getMockBuilder(CacheInterface::class)
            ->getMockForAbstractClass();
        $this->resourceMock = $this->getMockBuilder(Website::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->connectionMock = $this->getMockBuilder(AdapterInterface::class)
            ->getMockForAbstractClass();

        $this->resourceMock->expects($this->any())
            ->method('getConnection')
            ->willReturn($this->connectionMock);
        $this->processorFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->processorMock);

        $this->model = new Importer(
            $this->dataDifferenceCalculatorMock,
            $this->processorFactoryMock,
            $this->storeManagerMock,
            $this->cacheManagerMock,
            $this->resourceMock
        );
    }

    public function testImport()
    {
        $data = [];

        $this->connectionMock->expects($this->once())
            ->method('beginTransaction');
        $this->processorMock->expects($this->exactly(3))
            ->method('run')
            ->with($data);
        $this->connectionMock->expects($this->once())
            ->method('commit');
        $this->storeManagerMock->expects($this->once())
            ->method('reinitStores');
        $this->cacheManagerMock->expects($this->once())
            ->method('clean');

        $this->model->import($data);
    }

    /**
     * @expectedException \Magento\Framework\Exception\State\InvalidTransitionException
     * @expectedExceptionMessage Some error
     */
    public function testImportWithException()
    {
        $data = [];

        $this->connectionMock->expects($this->once())
            ->method('beginTransaction');
        $this->processorMock->expects($this->any())
            ->method('run')
            ->willThrowException(new \Exception('Some error'));
        $this->connectionMock->expects($this->never())
            ->method('commit');
        $this->storeManagerMock->expects($this->once())
            ->method('reinitStores');
        $this->cacheManagerMock->expects($this->once())
            ->method('clean');

        $this->model->import($data);
    }

    public function testGetWarningMessages()
    {
        $expectedData = [
            'Next Stores will be deleted: 3 stores',
            'Next Groups will be deleted: 2 groups',
            'Next Websites will be deleted: 1 website',
            'Next Websites will be updated: 7 websites',
        ];
        $data = [
            ScopeInterface::SCOPE_STORES => ['stores'],
            ScopeInterface::SCOPE_GROUPS => ['groups'],
            ScopeInterface::SCOPE_WEBSITES => ['websites'],
        ];

        $this->dataDifferenceCalculatorMock->expects($this->exactly(3))
            ->method('getItemsToDelete')
            ->willReturnMap([
                [ScopeInterface::SCOPE_STORES, ['stores'], [['name' => '3 stores']]],
                [ScopeInterface::SCOPE_GROUPS, ['groups'], [['name' => '2 groups']]],
                [ScopeInterface::SCOPE_WEBSITES, ['websites'], [['name' => '1 website']]],
            ]);
        $this->dataDifferenceCalculatorMock->expects($this->exactly(3))
            ->method('getItemsToUpdate')
            ->willReturnMap([
                [ScopeInterface::SCOPE_WEBSITES, ['websites'], [['name' => '7 websites']]],
            ]);

        $this->assertSame($expectedData, $this->model->getWarningMessages($data));
    }
}

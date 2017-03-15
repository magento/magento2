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
     * @var \Magento\Store\Model\Config\Importer\Processor\ProcessorFactory|Mock
     */
    private $processFactoryMock;

    /**
     * @var \Magento\Store\Model\Config\Importer\Processor\ProcessorInterface|Mock
     */
    private $processMock;

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
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->dataDifferenceCalculatorMock = $this->getMockBuilder(Importer\DataDifferenceCalculator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->processFactoryMock = $this->getMockBuilder(Importer\Processor\ProcessorFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->processMock = $this->getMockBuilder(Importer\Processor\ProcessorInterface::class)
            ->getMockForAbstractClass();
        $this->storeManagerMock = $this->getMockBuilder(StoreManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->cacheManagerMock = $this->getMockBuilder(CacheInterface::class)
            ->getMockForAbstractClass();
        $this->resourceMock = $this->getMockBuilder(Website::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = new Importer(
            $this->dataDifferenceCalculatorMock,
            $this->processFactoryMock,
            $this->storeManagerMock,
            $this->cacheManagerMock,
            $this->resourceMock
        );
    }

    public function testGetWarningMessages()
    {
        $expectedData = [
            'Next Stores will be deleted: 3 Stores',
            'Next Groups will be deleted: 2 groups',
            'Next Websites will be deleted: 1 website'
        ];
        $data = [
            ScopeInterface::SCOPE_STORES => ['stores'],
            ScopeInterface::SCOPE_GROUPS => ['groups'],
            ScopeInterface::SCOPE_WEBSITES => ['websites'],
        ];

        $this->dataDifferenceCalculatorMock->expects($this->exactly(3))
            ->method('getItemsToDelete')
            ->willReturnMap([
                [ScopeInterface::SCOPE_STORES, ['stores'], [['name' => '3 Stores']]],
                [ScopeInterface::SCOPE_GROUPS, ['groups'], [['name' => '2 groups']]],
                [ScopeInterface::SCOPE_WEBSITES, ['websites'], [['name' => '1 website']]],
            ]);

        $this->assertSame($expectedData, $this->model->getWarningMessages($data));
    }
}

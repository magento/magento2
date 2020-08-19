<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Indexer\Product\Price\System\Config;

use Magento\Catalog\Model\Indexer\Product\Price\Processor;
use Magento\Catalog\Model\Indexer\Product\Price\System\Config\PriceScope;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Indexer\Model\Indexer;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PriceScopeTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    protected $_objectManager;

    /**
     * @var PriceScope
     */
    protected $_model;

    /**
     * @var Indexer|MockObject
     */
    protected $_indexerMock;

    /**
     * @var IndexerRegistry|MockObject
     */
    protected $indexerRegistryMock;

    protected function setUp(): void
    {
        $this->_objectManager = new ObjectManager($this);

        $this->_indexerMock = $this->createPartialMock(Indexer::class, ['load', 'invalidate']);
        $this->indexerRegistryMock = $this->createPartialMock(
            IndexerRegistry::class,
            ['get']
        );

        $contextMock = $this->createMock(Context::class);
        $registryMock = $this->createMock(Registry::class);
        $storeManagerMock = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $configMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);

        $this->_model = $this->_objectManager->getObject(
            PriceScope::class,
            [
                'context' => $contextMock,
                'registry' => $registryMock,
                'storeManager' => $storeManagerMock,
                'config' => $configMock,
                'indexerRegistry' => $this->indexerRegistryMock
            ]
        );
    }

    public function testProcessValue()
    {
        $this->_indexerMock->expects($this->once())->method('invalidate');
        $this->prepareIndexer(1);
        $this->_model->setValue('1');
        $this->_model->processValue();
    }

    public function testProcessValueNotChanged()
    {
        $this->_indexerMock->expects($this->never())->method('invalidate');
        $this->prepareIndexer(0);
        $this->_model->processValue();
    }

    /**
     * @param int $countCall
     */
    protected function prepareIndexer($countCall)
    {
        $this->indexerRegistryMock->expects($this->exactly($countCall))
            ->method('get')
            ->with(Processor::INDEXER_ID)
            ->willReturn($this->_indexerMock);
    }
}

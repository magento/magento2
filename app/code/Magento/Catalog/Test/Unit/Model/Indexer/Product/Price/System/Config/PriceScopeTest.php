<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\Indexer\Product\Price\System\Config;

class PriceScopeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $_objectManager;

    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Price\System\Config\PriceScope
     */
    protected $_model;

    /**
     * @var \Magento\Indexer\Model\Indexer|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_indexerMock;

    /**
     * @var \Magento\Framework\Indexer\IndexerRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $indexerRegistryMock;

    protected function setUp()
    {
        $this->_objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->_indexerMock = $this->createPartialMock(\Magento\Indexer\Model\Indexer::class, ['load', 'invalidate']);
        $this->indexerRegistryMock = $this->createPartialMock(
            \Magento\Framework\Indexer\IndexerRegistry::class,
            ['get']
        );

        $contextMock = $this->createMock(\Magento\Framework\Model\Context::class);
        $registryMock = $this->createMock(\Magento\Framework\Registry::class);
        $storeManagerMock = $this->createMock(\Magento\Store\Model\StoreManagerInterface::class);
        $configMock = $this->createMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);

        $this->_model = $this->_objectManager->getObject(
            \Magento\Catalog\Model\Indexer\Product\Price\System\Config\PriceScope::class,
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
            ->with(\Magento\Catalog\Model\Indexer\Product\Price\Processor::INDEXER_ID)
            ->will($this->returnValue($this->_indexerMock));
    }
}

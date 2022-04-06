<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Test\Unit\Ui\DataProvider\Product;

use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\ConfigurableProduct\Ui\DataProvider\Product\ConfigurableDataProvider;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Ui\DataProvider\Modifier\PoolInterface;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use PHPUnit\Framework\TestCase;

/**
 * ConfigurableDataProviderTest class which checks type
 */
class ConfigurableDataProviderTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var CollectionFactory|MockObject
     */
    private $collectionFactoryMock;

    /**
     * @var Collection|MockObject
     */
    private $collectionMock;

    /**
     * @var PoolInterface|MockObject
     */
    private $modifiersPool;

    /**
     * @var ModifierInterface|MockObject
     */
    private $modifierMockOne;

    /**
     * Test checks ConfigurableDataProvider type
     */
    public function testCheckType(): void
    {
        $this->assertInstanceOf(ConfigurableDataProvider::class, $this->getModel());
    }

    /**
     * Test checks collection type
     */
    public function testGetCollection(): void
    {
        $this->assertInstanceOf(Collection::class, $this->getModel()->getCollection());
    }

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        $this->collectionMock = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'toArray',
                    'isLoaded',
                    'addAttributeToFilter',
                    'load',
                    'getSize',
                    'addFilterByRequiredOptions',
                    'addStoreFilter'
                ]
            )->getMock();
        $this->collectionFactoryMock = $this->getMockBuilder(CollectionFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->collectionFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->collectionMock);

        $this->modifiersPool = $this->getMockBuilder(PoolInterface::class)
            ->getMockForAbstractClass();
        $this->modifierMockOne = $this->getMockBuilder(ModifierInterface::class)
            ->setMethods(['modifyData'])
            ->getMockForAbstractClass();
        $this->modifierMockOne->expects($this->any())
            ->method('modifyData')
            ->willReturn($this->returnArgument(0));
        $this->modifiersPool->expects($this->any())
            ->method('getModifiersInstances')
            ->willReturn([$this->modifierMockOne]);
    }

    /**
     * @return object
     */
    private function getModel(): ConfigurableDataProvider
    {
        return $this->objectManager->getObject(ConfigurableDataProvider::class, [
            'name' => 'testName',
            'primaryFieldName' => 'testPrimaryFieldName',
            'requestFieldName' => 'testRequestFieldName',
            'collectionFactory' => $this->collectionFactoryMock,
            'meta' => [],
            'data' => [],
            'addFieldStrategies' => [],
            'addFilterStrategies' => [],
            'modifiersPool' => $this->modifiersPool
        ]);
    }
}

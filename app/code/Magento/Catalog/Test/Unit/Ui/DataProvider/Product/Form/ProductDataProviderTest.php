<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Ui\DataProvider\Product\Form;

use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Ui\DataProvider\Product\Form\ProductDataProvider;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use Magento\Ui\DataProvider\Modifier\Pool;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ProductDataProviderTest extends TestCase
{
    use MockCreationTrait;
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var CollectionFactory|MockObject
     */
    protected $collectionFactoryMock;

    /**
     * @var Collection|MockObject
     */
    protected $collectionMock;

    /**
     * @var ModifierInterface|MockObject
     */
    protected $modifierMockOne;

    /**
     * @var Pool|MockObject
     */
    protected $poolMock;

    /**
     * @var ProductDataProvider
     */
    protected $model;

    /**
     * @var array
     */
    protected $modifierData = [];

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->collectionMock = $this->createMock(Collection::class);
        $this->collectionFactoryMock = $this->createMock(CollectionFactory::class);
        $this->collectionFactoryMock->method('create')->willReturn($this->collectionMock);
        $this->poolMock = $this->createMock(Pool::class);
        
        $this->modifierMockOne = $this->createMock(ModifierInterface::class);
        $this->modifierMockOne->method('modifyMeta')->willReturnCallback(
            function ($meta) {
                return $this->modifierData['meta'] ?? $meta;
            }
        );
        $this->modifierMockOne->method('modifyData')->willReturnCallback(
            function ($data) {
                return $this->modifierData['data'] ?? $data;
            }
        );

        $this->model = $this->objectManager->getObject(ProductDataProvider::class, [
            'name' => 'testName',
            'primaryFieldName' => 'testPrimaryFieldName',
            'requestFieldName' => 'testRequestFieldName',
            'collectionFactory' => $this->collectionFactoryMock,
            'pool' => $this->poolMock,
        ]);
    }

    public function testGetMeta()
    {
        $expectedMeta = ['meta_key' => 'meta_value'];

        $this->poolMock->expects($this->once())
            ->method('getModifiersInstances')
            ->willReturn([$this->modifierMockOne]);
        $this->modifierData['meta'] = $expectedMeta;

        $this->assertSame($expectedMeta, $this->model->getMeta());
    }

    public function testGetData()
    {
        $expectedMeta = ['data_key' => 'data_value'];

        $this->poolMock->expects($this->once())
            ->method('getModifiersInstances')
            ->willReturn([$this->modifierMockOne]);
        $this->modifierData['data'] = $expectedMeta;

        $this->assertSame($expectedMeta, $this->model->getData());
    }
}

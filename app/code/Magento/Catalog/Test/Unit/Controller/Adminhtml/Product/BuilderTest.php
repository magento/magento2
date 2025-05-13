<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Controller\Adminhtml\Product;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Controller\Adminhtml\Product\Builder;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type as ProductTypes;
use Magento\Catalog\Model\ProductFactory;
use Magento\Cms\Model\Wysiwyg\Config as WysiwygConfig;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class BuilderTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var Builder
     */
    protected $builder;

    /**
     * @var MockObject
     */
    protected $loggerMock;

    /**
     * @var MockObject
     */
    protected $productFactoryMock;

    /**
     * @var MockObject
     */
    protected $registryMock;

    /**
     * @var MockObject
     */
    protected $wysiwygConfigMock;

    /**
     * @var MockObject
     */
    protected $requestMock;

    /**
     * @var MockObject
     */
    protected $productMock;

    /**
     * @var StoreFactory|MockObject
     */
    protected $storeFactoryMock;

    /**
     * @var ProductRepositoryInterface|MockObject
     */
    protected $productRepositoryMock;

    /**
     * @var StoreInterface|MockObject
     */
    protected $storeMock;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->loggerMock = $this->getMockForAbstractClass(LoggerInterface::class);
        $this->productFactoryMock = $this->createPartialMock(ProductFactory::class, ['create']);
        $this->registryMock = $this->createMock(Registry::class);
        $this->wysiwygConfigMock = $this->getMockBuilder(WysiwygConfig::class)
            ->addMethods(['setStoreId'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->requestMock = $this->createMock(Http::class);
        $methods = ['setStoreId', 'setData', 'load', 'setAttributeSetId', 'setTypeId'];
        $this->productMock = $this->createPartialMock(Product::class, $methods);
        $this->storeFactoryMock = $this->getMockBuilder(StoreFactory::class)
            ->onlyMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeMock = $this->getMockBuilder(StoreInterface::class)
            ->addMethods(['load'])
            ->getMockForAbstractClass();

        $this->productRepositoryMock = $this->getMockBuilder(ProductRepositoryInterface::class)
            ->onlyMethods(['getById'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->builder = $this->objectManager->getObject(
            Builder::class,
            [
                'productFactory' => $this->productFactoryMock,
                'logger' => $this->loggerMock,
                'registry' => $this->registryMock,
                'wysiwygConfig' => $this->wysiwygConfigMock,
                'storeFactory' => $this->storeFactoryMock,
                'productRepository' => $this->productRepositoryMock
            ]
        );
    }

    public function testBuildWhenProductExistAndPossibleToLoadProduct()
    {
        $productId = 2;
        $productType = 'type_id';
        $productStore = 'store';
        $productSet = 3;

        $valueMap = [
            ['id', null, $productId],
            ['type', null, $productType],
            ['set', null, $productSet],
            ['store', 0, $productStore],
        ];

        $this->requestMock->expects($this->any())
            ->method('getParam')
            ->willReturnMap($valueMap);

        $this->productRepositoryMock->expects($this->any())
            ->method('getById')
            ->with($productId, true, $productStore)
            ->willReturn($this->productMock);

        $this->storeFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->storeMock);

        $this->storeMock->expects($this->any())
            ->method('load')
            ->with($productStore)
            ->willReturnSelf();

        $registryValueMap = [
            ['product', $this->productMock, $this->registryMock],
            ['current_product', $this->productMock, $this->registryMock],
            ['current_store', $this->registryMock, $this->storeMock],
        ];

        $this->registryMock->expects($this->any())
            ->method('register')
            ->willReturn($registryValueMap);

        $this->wysiwygConfigMock->expects($this->once())
            ->method('setStoreId')
            ->with($productStore);

        $this->assertEquals($this->productMock, $this->builder->build($this->requestMock));
    }

    public function testBuildWhenImpossibleLoadProduct()
    {
        $productId = 2;
        $productType = 'type_id';
        $productStore = 'store';
        $productSet = 3;

        $valueMap = [
            ['id', null, $productId],
            ['type', null, $productType],
            ['set', null, $productSet],
            ['store', 0, $productStore],
        ];

        $this->requestMock->expects($this->any())
            ->method('getParam')
            ->willReturnMap($valueMap);

        $this->productRepositoryMock->expects($this->any())
            ->method('getById')
            ->with($productId, true, $productStore)
            ->willThrowException(new NoSuchEntityException());

        $this->productFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->productMock);

        $this->productMock->expects($this->any())
            ->method('setData')
            ->with('_edit_mode', true);

        $this->productMock->expects($this->any())
            ->method('setTypeId')
            ->with(ProductTypes::DEFAULT_TYPE);

        $this->productMock->expects($this->any())
            ->method('setStoreId')
            ->with($productStore);

        $this->productMock->expects($this->any())
            ->method('setAttributeSetId')
            ->with($productSet);

        $this->loggerMock->expects($this->once())
            ->method('critical');

        $this->storeFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->storeMock);

        $this->storeMock->expects($this->any())
            ->method('load')
            ->with($productStore)
            ->willReturnSelf();

        $registryValueMap = [
            ['product', $this->productMock, $this->registryMock],
            ['current_product', $this->productMock, $this->registryMock],
            ['current_store', $this->registryMock, $this->storeMock],
        ];

        $this->registryMock->expects($this->any())
            ->method('register')
            ->willReturn($registryValueMap);

        $this->wysiwygConfigMock->expects($this->once())
            ->method('setStoreId')
            ->with($productStore);

        $this->assertEquals($this->productMock, $this->builder->build($this->requestMock));
    }

    public function testBuildWhenProductNotExist()
    {
        $productId = 0;
        $productType = 'type_id';
        $productStore = 'store';
        $productSet = 3;

        $valueMap = [
            ['id', null, $productId],
            ['type', null, $productType],
            ['set', null, $productSet],
            ['store', 0, $productStore],
        ];

        $this->requestMock->expects($this->any())
            ->method('getParam')
            ->willReturnMap($valueMap);

        $this->productRepositoryMock->expects($this->any())
            ->method('getById')
            ->with($productId, true, $productStore)
            ->willThrowException(new NoSuchEntityException());

        $this->productFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->productMock);

        $this->productMock->expects($this->any())
            ->method('setData')
            ->with('_edit_mode', true);

        $this->productMock->expects($this->any())
            ->method('setTypeId')
            ->with($productType);

        $this->productMock->expects($this->any())
            ->method('setStoreId')
            ->with($productStore);

        $this->productMock->expects($this->any())
            ->method('setAttributeSetId')
            ->with($productSet);

        $this->storeFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->storeMock);

        $this->storeMock->expects($this->any())
            ->method('load')
            ->with($productStore)
            ->willReturnSelf();

        $registryValueMap = [
            ['product', $this->productMock, $this->registryMock],
            ['current_product', $this->productMock, $this->registryMock],
            ['current_store', $this->registryMock, $this->storeMock],
        ];

        $this->registryMock->expects($this->any())
            ->method('register')
            ->willReturn($registryValueMap);

        $this->wysiwygConfigMock->expects($this->once())
            ->method('setStoreId')
            ->with($productStore);

        $this->assertEquals($this->productMock, $this->builder->build($this->requestMock));
    }
}

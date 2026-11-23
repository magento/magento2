<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Product\ProductFrontendAction;

use Magento\Catalog\Api\Data\ProductFrontendActionInterface;
use Magento\Catalog\Model\FrontendStorageConfigurationInterface;
use Magento\Catalog\Model\FrontendStorageConfigurationPool;
use Magento\Catalog\Model\Product\ProductFrontendAction\Synchronizer;
use Magento\Catalog\Model\ProductFrontendActionFactory;
use Magento\Catalog\Model\ResourceModel\ProductFrontendAction\Collection;
use Magento\Catalog\Model\ResourceModel\ProductFrontendAction\CollectionFactory;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\Visitor;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SynchronizerTest extends TestCase
{
    /**
     * @var Synchronizer
     */
    protected $model;

    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var Session
     */
    protected $sessionMock;

    /**
     * @var Visitor|MockObject
     */
    protected $visitorMock;

    /**
     * @var ProductFrontendActionFactory|MockObject
     */
    protected $productFrontendActionFactoryMock;

    /**
     * @var EntityManager|MockObject
     */
    protected $entityManagerMock;

    /**
     * @var MockObject
     */
    protected $collectionFactoryMock;

    /**
     * @var MockObject
     */
    protected $frontendStorageConfigurationPoolMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->sessionMock = $this->createStub(Session::class);
        $this->visitorMock = $this->createMock(Visitor::class);
        $this->productFrontendActionFactoryMock = $this->createPartialMock(
            ProductFrontendActionFactory::class,
            ['create']
        );
        $this->entityManagerMock = $this->createMock(EntityManager::class);
        $this->collectionFactoryMock = $this->createPartialMock(CollectionFactory::class, ['create']);
        $this->frontendStorageConfigurationPoolMock = $this->createMock(FrontendStorageConfigurationPool::class);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $this->objectManagerHelper->getObject(
            Synchronizer::class,
            [
                'session' => $this->sessionMock,
                'visitor' => $this->visitorMock,
                'productFrontendActionFactory' => $this->productFrontendActionFactoryMock,
                'entityManager' => $this->entityManagerMock,
                'collectionFactory' => $this->collectionFactoryMock,
                'frontendStorageConfigurationPool' => $this->frontendStorageConfigurationPoolMock
            ]
        );
    }

    /**
     * @inheritDoc
     */
    #[DataProvider('existingProductsDataProvider')]
    public function testFilterProductActions(array $existingProductIds, int $expectedDeletes): void
    {
        $productsData = [
            'website-1-1' => [
                'added_at' => 12,
                'product_id' => 1
            ],
            'website-1-2' => [
                'added_at' => 13,
                'product_id' => '2'
            ],
            'website-2-3' => [
                'added_at' => 14,
                'product_id' => 3
            ]
        ];
        $frontendConfiguration = $this->createMock(FrontendStorageConfigurationInterface::class);
        $frontendConfiguration->expects($this->once())
            ->method('get')
            ->willReturn([
                'lifetime' => 2
            ]);
        $this->frontendStorageConfigurationPoolMock->expects($this->once())
            ->method('get')
            ->with('recently_compared_product')
            ->willReturn($frontendConfiguration);
        $action1 = $this->createStub(ProductFrontendActionInterface::class);
        $action2 = $this->createStub(ProductFrontendActionInterface::class);

        $frontendAction = $this->createStub(ProductFrontendActionInterface::class);
        $frontendAction->method('getActionId')->willReturn(1);
        $collection = $this->createMock(Collection::class);
        $collection->method('getColumnValues')->with('existing_product_id')->willReturn($existingProductIds);
        $this->sessionMock->method('getCustomerId')->willReturn(1);
        $this->visitorMock->expects($this->exactly(2))
            ->method('getId')
            ->willReturn(34);
        $this->collectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($collection);
        $collection->expects($this->once())
            ->method('addFilterByUserIdentities')
            ->with(1, 34);
        $iterator = new \IteratorIterator(
            new \ArrayIterator([$frontendAction, $this->createStub(ProductFrontendActionInterface::class)])
        );
        $collection->expects($this->exactly($expectedDeletes))
            ->method('getIterator')
            ->willReturn($iterator);
        $this->entityManagerMock->expects($this->exactly($expectedDeletes))
            ->method('delete')
            ->with($frontendAction);
        $this->productFrontendActionFactoryMock->expects($this->exactly(count($existingProductIds)))
            ->method('create')
            ->willReturnCallback(function ($args) use ($action1, $action2, $existingProductIds) {
                $this->assertContains((int) $args['data']['product_id'], $existingProductIds);
                if ($args['data']['added_at'] === 12) {
                    return $action1;
                } elseif ($args['data']['added_at'] === 13) {
                    return $action2;
                }
            });
        $this->entityManagerMock->expects($this->exactly(count($existingProductIds)))
            ->method('save')
            ->with($this->logicalOr($this->identicalTo($action1), $this->identicalTo($action2)));
        $this->model->syncActions($productsData, 'recently_compared_product');
    }

    public static function existingProductsDataProvider(): array
    {
        return [
            'valid products' => [[1, 2], 1],
            'mixed batch' => [[1], 1],
            'all deleted' => [[], 0],
        ];
    }
}

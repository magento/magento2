<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\System\Config\Backend\Rss;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Model\System\Config\Backend\Rss\Category;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CategoryTest extends TestCase
{
    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $configMock;

    /**
     * @var ProductAttributeRepositoryInterface|MockObject
     */
    private $productAttributeRepositoryMock;

    /**
     * @var Category
     */
    private $model;

    protected function setUp(): void
    {
        $contextMock = $this->createMock(Context::class);
        $eventManagerMock = $this->createMock(EventManager::class);
        $contextMock->method('getEventDispatcher')
            ->willReturn($eventManagerMock);
        $registryMock = $this->createMock(Registry::class);
        $this->configMock = $this->createMock(ScopeConfigInterface::class);
        $cacheTypeListMock = $this->createMock(TypeListInterface::class);
        $resourceMock = $this->createMock(AbstractResource::class);
        $resourceCollectionMock = $this->createMock(AbstractDb::class);
        $this->productAttributeRepositoryMock = $this->createMock(ProductAttributeRepositoryInterface::class);
        $this->model = new Category(
            $contextMock,
            $registryMock,
            $this->configMock,
            $cacheTypeListMock,
            $resourceMock,
            $resourceCollectionMock,
            ['path' => 'rss/catalog/category'],
            $this->productAttributeRepositoryMock
        );
    }

    /**
     * @dataProvider afterSaveDataProvider
     * @param string $oldValue
     * @param string $newValue
     * @param bool $isUsedForSort
     * @param bool $isUpdateNeeded
     */
    public function testAfterSave(string $oldValue, string $newValue, bool $isUsedForSort, bool $isUpdateNeeded): void
    {
        $this->configMock->expects($this->atLeastOnce())
            ->method('getValue')
            ->with('rss/catalog/category', 'default', null)
            ->willReturn($oldValue);

        $productAttributeMock = $this->createMock(ProductAttributeInterface::class);
        $productAttributeMock->method('getUsedForSortBy')
            ->willReturn($isUsedForSort);
        $this->productAttributeRepositoryMock->method('get')
            ->with('updated_at')
            ->willReturn($productAttributeMock);

        $productAttributeMock->expects($this->exactly((int) $isUpdateNeeded))
            ->method('setUsedForSortBy')
            ->with(true)
            ->willReturnSelf();
        $this->productAttributeRepositoryMock->expects($this->exactly((int) $isUpdateNeeded))
            ->method('save')
            ->with($productAttributeMock)
            ->willReturn($productAttributeMock);

        $this->model->setValue($newValue);
        $this->model->afterSave();
    }

    public function afterSaveDataProvider(): array
    {
        return [
            ['0', '1', false, true],
            ['0', '0', false, false],
            ['1', '0', false, false],
            ['0', '1', true, false],
        ];
    }
}

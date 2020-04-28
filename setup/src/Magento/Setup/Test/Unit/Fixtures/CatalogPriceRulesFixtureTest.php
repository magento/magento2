<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Fixtures;

use Magento\Catalog\Model\Category;
use Magento\CatalogRule\Api\Data\RuleInterface;
use Magento\CatalogRule\Model\Rule;
use Magento\Framework\EntityManager\EntityMetadata;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\ObjectManager\ObjectManager;
use Magento\Setup\Fixtures\CatalogPriceRulesFixture;
use Magento\Setup\Fixtures\FixtureModel;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManager;
use Magento\Store\Model\Website;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CatalogPriceRulesFixtureTest extends TestCase
{
    /**
     * @var MockObject|FixtureModel
     */
    private $fixtureModelMock;

    /**
     * @var CatalogPriceRulesFixture
     */
    private $model;

    protected function setUp(): void
    {
        $this->fixtureModelMock = $this->createMock(FixtureModel::class);

        $this->model = new CatalogPriceRulesFixture($this->fixtureModelMock);
    }

    public function testExecute()
    {
        $storeMock = $this->createMock(Store::class);
        $storeMock->expects($this->once())
            ->method('getRootCategoryId')
            ->willReturn(2);

        $websiteMock = $this->createMock(Website::class);
        $websiteMock->expects($this->once())
            ->method('getGroups')
            ->willReturn([$storeMock]);
        $websiteMock->expects($this->once())
            ->method('getId')
            ->willReturn('website_id');

        $storeManagerMock = $this->createMock(StoreManager::class);
        $storeManagerMock->expects($this->once())
            ->method('getWebsites')
            ->willReturn([$websiteMock]);

        $contextMock = $this->createMock(Context::class);
        $abstractDbMock = $this->getMockForAbstractClass(
            AbstractDb::class,
            [$contextMock],
            '',
            true,
            true,
            true,
            ['getAllChildren']
        );
        $abstractDbMock->expects($this->once())
            ->method('getAllChildren')
            ->willReturn([1]);

        $categoryMock = $this->createMock(Category::class);
        $categoryMock->expects($this->once())
            ->method('getResource')
            ->willReturn($abstractDbMock);
        $categoryMock->expects($this->once())
            ->method('getPath')
            ->willReturn('path/to/file');
        $categoryMock->expects($this->once())
            ->method('getId')
            ->willReturn('category_id');

        $modelMock = $this->createMock(Rule::class);
        $metadataMock = $this->createMock(EntityMetadata::class);
        $metadataPoolMock = $this->createMock(MetadataPool::class);
        $metadataMock->expects($this->once())
            ->method('getLinkField')
            ->willReturn('Field Id Name');

        $valueMap = [
            [Rule::class, $modelMock],
            [Category::class, $categoryMock],
            [MetadataPool::class, $metadataPoolMock]
        ];
        $metadataPoolMock
            ->expects($this->once())
            ->method('getMetadata')
            ->with(RuleInterface::class)
            ->willReturn($metadataMock);
        $objectManagerMock = $this->createMock(ObjectManager::class);
        $objectManagerMock->expects($this->once())
            ->method('create')
            ->willReturn($storeManagerMock);
        $objectManagerMock->expects($this->exactly(3))
            ->method('get')
            ->willReturnMap($valueMap);

        $this->fixtureModelMock
            ->expects($this->once())
            ->method('getValue')
            ->willReturn(1);
        $this->fixtureModelMock
            ->expects($this->exactly(4))
            ->method('getObjectManager')
            ->willReturn($objectManagerMock);

        $this->model->execute();
    }

    public function testNoFixtureConfigValue()
    {
        $ruleMock = $this->createMock(\Magento\SalesRule\Model\Rule::class);
        $ruleMock->expects($this->never())->method('save');

        $objectManagerMock = $this->createMock(ObjectManager::class);
        $objectManagerMock->expects($this->never())
            ->method('get')
            ->with(\Magento\SalesRule\Model\Rule::class)
            ->willReturn($ruleMock);

        $this->fixtureModelMock
            ->expects($this->never())
            ->method('getObjectManager')
            ->willReturn($objectManagerMock);
        $this->fixtureModelMock
            ->expects($this->once())
            ->method('getValue')
            ->willReturn(false);

        $this->model->execute();
    }

    public function testGetActionTitle()
    {
        $this->assertSame('Generating catalog price rules', $this->model->getActionTitle());
    }

    public function testIntroduceParamLabels()
    {
        $this->assertSame([
            'catalog_price_rules' => 'Catalog Price Rules'
        ], $this->model->introduceParamLabels());
    }
}

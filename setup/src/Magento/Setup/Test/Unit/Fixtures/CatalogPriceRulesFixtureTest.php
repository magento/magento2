<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Fixtures;

use \Magento\Setup\Fixtures\CatalogPriceRulesFixture;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CatalogPriceRulesFixtureTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Setup\Fixtures\FixtureModel
     */
    private $fixtureModelMock;

    /**
     * @var \Magento\Setup\Fixtures\CatalogPriceRulesFixture
     */
    private $model;

    public function setUp()
    {
        $this->fixtureModelMock = $this->createMock(\Magento\Setup\Fixtures\FixtureModel::class);

        $this->model = new CatalogPriceRulesFixture($this->fixtureModelMock);
    }

    public function testExecute()
    {
        $storeMock = $this->createMock(\Magento\Store\Model\Store::class);
        $storeMock->expects($this->once())
            ->method('getRootCategoryId')
            ->will($this->returnValue(2));

        $websiteMock = $this->createMock(\Magento\Store\Model\Website::class);
        $websiteMock->expects($this->once())
            ->method('getGroups')
            ->will($this->returnValue([$storeMock]));
        $websiteMock->expects($this->once())
            ->method('getId')
            ->will($this->returnValue('website_id'));

        $storeManagerMock = $this->createMock(\Magento\Store\Model\StoreManager::class);
        $storeManagerMock->expects($this->once())
            ->method('getWebsites')
            ->will($this->returnValue([$websiteMock]));

        $contextMock = $this->createMock(\Magento\Framework\Model\ResourceModel\Db\Context::class);
        $abstractDbMock = $this->getMockForAbstractClass(
            \Magento\Framework\Model\ResourceModel\Db\AbstractDb::class,
            [$contextMock],
            '',
            true,
            true,
            true,
            ['getAllChildren']
        );
        $abstractDbMock->expects($this->once())
            ->method('getAllChildren')
            ->will($this->returnValue([1]));

        $categoryMock = $this->createMock(\Magento\Catalog\Model\Category::class);
        $categoryMock->expects($this->once())
            ->method('getResource')
            ->will($this->returnValue($abstractDbMock));
        $categoryMock->expects($this->once())
            ->method('getPath')
            ->will($this->returnValue('path/to/file'));
        $categoryMock->expects($this->once())
            ->method('getId')
            ->will($this->returnValue('category_id'));

        $modelMock = $this->createMock(\Magento\CatalogRule\Model\Rule::class);
        $metadataMock = $this->createMock(\Magento\Framework\EntityManager\EntityMetadata::class);
        $metadataPoolMock = $this->createMock(\Magento\Framework\EntityManager\MetadataPool::class);
        $metadataMock->expects($this->once())
            ->method('getLinkField')
            ->will($this->returnValue('Field Id Name'));

        $valueMap = [
            [\Magento\CatalogRule\Model\Rule::class, $modelMock],
            [\Magento\Catalog\Model\Category::class, $categoryMock],
            [\Magento\Framework\EntityManager\MetadataPool::class, $metadataPoolMock]
        ];
        $metadataPoolMock
            ->expects($this->once())
            ->method('getMetadata')
            ->with(\Magento\CatalogRule\Api\Data\RuleInterface::class)
            ->willReturn($metadataMock);
        $objectManagerMock = $this->createMock(\Magento\Framework\ObjectManager\ObjectManager::class);
        $objectManagerMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($storeManagerMock));
        $objectManagerMock->expects($this->exactly(3))
            ->method('get')
            ->will($this->returnValueMap($valueMap));

        $this->fixtureModelMock
            ->expects($this->once())
            ->method('getValue')
            ->will($this->returnValue(1));
        $this->fixtureModelMock
            ->expects($this->exactly(4))
            ->method('getObjectManager')
            ->will($this->returnValue($objectManagerMock));

        $this->model->execute();
    }

    public function testNoFixtureConfigValue()
    {
        $ruleMock = $this->createMock(\Magento\SalesRule\Model\Rule::class);
        $ruleMock->expects($this->never())->method('save');

        $objectManagerMock = $this->createMock(\Magento\Framework\ObjectManager\ObjectManager::class);
        $objectManagerMock->expects($this->never())
            ->method('get')
            ->with($this->equalTo(\Magento\SalesRule\Model\Rule::class))
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

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Fixtures;

use Magento\Catalog\Api\AttributeSetRepositoryInterface;
use Magento\Catalog\Api\ProductAttributeOptionManagementInterface;
use Magento\Catalog\Model\Category;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\ImportExport\Model\Import;
use Magento\Setup\Fixtures\AttributeSet\AttributeSetFixture;
use Magento\Setup\Fixtures\AttributeSet\Pattern;
use Magento\Setup\Fixtures\ConfigurableProductsFixture;
use Magento\Setup\Fixtures\FixtureModel;
use Magento\Setup\Model\Complex\Generator;
use Magento\Store\Model\StoreManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ConfigurableProductsFixtureTest extends TestCase
{
    /**
     * @var MockObject|FixtureModel
     */
    private $fixtureModelMock;

    /**
     * @var ConfigurableProductsFixture
     */
    private $model;

    /**
     * @var AttributeSetFixture
     */
    private $attributeSetsFixtureMock;

    /**
     * @var Pattern
     */
    private $attributePatternMock;

    protected function setUp(): void
    {
        $this->fixtureModelMock = $this->getMockBuilder(FixtureModel::class)
            ->disableOriginalConstructor()
            ->setMethods(['createAttributeSet', 'getValue', 'getObjectManager'])
            ->getMock();

        $this->attributeSetsFixtureMock = $this->getMockBuilder(AttributeSetFixture::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->attributePatternMock = $this->getMockBuilder(Pattern::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = (new ObjectManager($this))->getObject(ConfigurableProductsFixture::class, [
            'fixtureModel' => $this->fixtureModelMock,
            'attributeSetsFixture' => $this->attributeSetsFixtureMock,
            'attributePattern' => $this->attributePatternMock,
        ]);
    }

    /**
     * @SuppressWarnings(PHPMD)
     */
    public function testExecute()
    {
        $importMock = $this->getMockBuilder(Import::class)
            ->disableOriginalConstructor()
            ->getMock();

        $categoryMock = $this->getMockBuilder(Category::class)
            ->disableOriginalConstructor()
            ->getMock();

        $storeManagerMock = $this->getMockBuilder(StoreManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $source = $this->getMockBuilder(Generator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManagerMock = $this->getMockBuilder(\Magento\Framework\ObjectManager\ObjectManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $attributeSetRepositoryMock = $this->getMockForAbstractClass(
            AttributeSetRepositoryInterface::class
        );

        $productAttributeOptionManagementInterface = $this->getMockForAbstractClass(
            ProductAttributeOptionManagementInterface::class
        );

        $objectManagerMock->expects($this->any())
            ->method('get')
            ->willReturnMap([
                [
                    StoreManager::class,
                    $storeManagerMock
                ],
                [
                    AttributeSetRepositoryInterface::class,
                    $attributeSetRepositoryMock
                ],
                [
                    ProductAttributeOptionManagementInterface::class,
                    $productAttributeOptionManagementInterface
                ]
            ]);

        $attributeCollectionFactoryMock = $this->getMockBuilder(CollectionFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $objectManagerMock->expects($this->any())
            ->method('create')
            ->willReturnCallback(
                function ($className) use (
                    $attributeCollectionFactoryMock,
                    $categoryMock,
                    $importMock,
                    $source
                ) {
                    if ($className === CollectionFactory::class) {
                        return $attributeCollectionFactoryMock;
                    }

                    if ($className === Category::class) {
                        return $categoryMock;
                    }

                    if ($className === Import::class) {
                        return $importMock;
                    }

                    if ($className === Generator::class) {
                        return $source;
                    }

                    return null;
                }
            );

        $valuesMap = [
            ['configurable_products', 0, 1],
            ['simple_products', 0, 1],
            ['search_terms', null, ['search_term' =>[['term' => 'iphone 6', 'count' => '1']]]],
            ['configurable_products_variation', 3, 1],
            [
                'search_config',
                null,
                [
                    'max_amount_of_words_description' => '200',
                    'max_amount_of_words_short_description' => '20',
                    'min_amount_of_words_description' => '20',
                    'min_amount_of_words_short_description' => '5'
                ]
            ],
            ['attribute_sets',
                null,
                [
                    'attribute_set' => [
                        [
                            'name' => 'attribute set name',
                            'attributes' => [
                                'attribute' => [
                                    [
                                        'is_required' => 1,
                                        'is_visible_on_front' => 1,
                                        'is_visible_in_advanced_search' => 1,
                                        'is_filterable' => 1,
                                        'is_filterable_in_search' => 1,
                                        'default_value' => 'yellow1',
                                        'attribute_code' => 'mycolor',
                                        'is_searchable' => '1',
                                        'frontend_label' => 'mycolor',
                                        'frontend_input' => 'select',
                                        'options' => [
                                            'option' => [
                                                [
                                                    'label' => 'yellow1',
                                                    'value' => ''
                                                ]
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $this->fixtureModelMock
            ->expects($this->any())
            ->method('getValue')
            ->willReturnMap($valuesMap);

        $this->model->execute();
    }

    public function testNoFixtureConfigValue()
    {
        $importMock = $this->getMockBuilder(Import::class)
            ->disableOriginalConstructor()
            ->getMock();
        $importMock->expects($this->never())->method('validateSource');
        $importMock->expects($this->never())->method('importSource');

        $objectManagerMock = $this->getMockBuilder(\Magento\Framework\ObjectManager\ObjectManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $objectManagerMock->expects($this->never())
            ->method('create')
            ->with(Import::class)
            ->willReturn($importMock);

        $this->fixtureModelMock
            ->expects($this->never())
            ->method('getObjectManager')
            ->willReturn($objectManagerMock);

        $this->model->execute();
    }

    public function testGetActionTitle()
    {
        $this->assertSame('Generating configurable products', $this->model->getActionTitle());
    }

    public function testIntroduceParamLabels()
    {
        $this->assertSame([], $this->model->introduceParamLabels());
    }
}

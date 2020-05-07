<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Fixtures;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute\Set;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Setup\Fixtures\EavVariationsFixture;
use Magento\Setup\Fixtures\FixtureModel;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for \Magento\Setup\Fixtures\EavVariationsFixture.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class EavVariationsFixtureTest extends TestCase
{
    /**
     * @var FixtureModel|MockObject
     */
    private $fixtureModelMock;

    /**
     * @var EavVariationsFixture
     */
    private $model;

    /**
     * @var StoreManager|MockObject
     */
    private $storeManagerMock;

    /**
     * @var Set|MockObject
     */
    private $attributeSetMock;

    /**
     * @var CacheInterface|MockObject
     */
    private $cacheMock;

    /**
     * @var Config|MockObject
     */
    private $eavConfigMock;

    /**
     * @var MockObject
     */
    private $attributeFactoryMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->fixtureModelMock = $this->getMockBuilder(FixtureModel::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->eavConfigMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManagerMock = $this->getMockBuilder(StoreManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->attributeSetMock = $this->getMockBuilder(Set::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->cacheMock = $this->getMockBuilder(CacheInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->attributeFactoryMock = $this->getMockBuilder(AttributeFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = (new ObjectManager($this))->getObject(
            EavVariationsFixture::class,
            [
                'fixtureModel' => $this->fixtureModelMock,
                'eavConfig' => $this->eavConfigMock,
                'storeManager' => $this->storeManagerMock,
                'attributeSet' => $this->attributeSetMock,
                'cache' => $this->cacheMock,
                'attributeFactory' => $this->attributeFactoryMock,
            ]
        );
    }

    /**
     * Test for execute method when attribute already exists.
     *
     * @return void
     */
    public function testDoNotExecuteWhenAttributeAlreadyExist()
    {
        $this->fixtureModelMock->expects($this->once())
            ->method('getValue')->with('configurable_products', [])->willReturn(10);
        $this->eavConfigMock->expects($this->once())->method('getEntityAttributeCodes')
            ->willReturn(['configurable_variation']);
        $this->attributeFactoryMock->expects($this->never())->method('create');

        $this->model->execute();
    }

    /**
     * Test for execute method.
     *
     * @return void
     */
    public function testExecute()
    {
        $storeId = 5;
        $this->eavConfigMock->expects($this->once())
            ->method('getEntityAttributeCodes')->willReturn(['attr1', 'attr2']);
        $this->fixtureModelMock
            ->expects($this->any())
            ->method('getValue')
            ->willReturnMap([
                ['configurable_products', [], ['some-config']],
                ['configurable_products_variation', 3, 1],
            ]);

        $storeMock = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManagerMock->expects($this->once())->method('getStores')->willReturn([$storeId => $storeMock]);
        $this->attributeSetMock->expects($this->once())->method('load')->willReturnSelf();
        $this->attributeSetMock->expects($this->once())->method('getDefaultGroupId')->willReturn(2);

        $attributeMock = $this->getMockBuilder(Attribute::class)
            ->setMethods([
                'setAttributeSetId',
                'setAttributeGroupId',
                'save',
            ])
            ->disableOriginalConstructor()
            ->getMock();
        $attributeMock->expects($this->exactly(2))->method('setAttributeSetId')->willReturnSelf();
        $attributeMock->expects($this->once())->method('setAttributeGroupId')->willReturnSelf();
        $this->attributeFactoryMock->expects($this->once())->method('create')
            ->with(
                [
                    'data' => [
                        'frontend_label' => [
                            $storeId => 'configurable variations',
                        ],
                        'frontend_input' => 'select',
                        'is_required' => '0',
                        'option' => [
                            'order' => ['option_1' => 1],
                            'value' => ['option_1' => [$storeId => 'option 1']],
                            'delete' => ['option_1' => ''],
                        ],
                        'default' => ['option_0'],
                        'attribute_code' => 'configurable_variation',
                        'is_global' => '1',
                        'default_value_text' => '',
                        'default_value_yesno' => '0',
                        'default_value_date' => '',
                        'default_value_textarea' => '',
                        'is_unique' => '0',
                        'is_searchable' => '1',
                        'is_visible_in_advanced_search' => '0',
                        'is_comparable' => '0',
                        'is_filterable' => '1',
                        'is_filterable_in_search' => '0',
                        'is_used_for_promo_rules' => '0',
                        'is_html_allowed_on_front' => '1',
                        'is_visible_on_front' => '0',
                        'used_in_product_listing' => '0',
                        'used_for_sort_by' => '0',
                        'source_model' => null,
                        'backend_model' => null,
                        'apply_to' => [],
                        'backend_type' => 'int',
                        'entity_type_id' => 4,
                        'is_user_defined' => 1,
                        'swatch_input_type' => 'visual',
                        'swatchvisual' => [
                            'value' => ['option_1' => '#ffffff'],
                        ],
                        'optionvisual' => [
                            'value' => ['option_1' => ['option 1']],
                        ],
                    ]
                ]
            )->willReturn($attributeMock);
        $this->cacheMock->expects($this->once())
            ->method('remove')->with(Config::ATTRIBUTES_CACHE_ID . Product::ENTITY);

        $this->model->execute();
    }

    /**
     * Test for getActionTitle method.
     *
     * @return void
     */
    public function testGetActionTitle()
    {
        $this->assertSame('Generating configurable EAV variations', $this->model->getActionTitle());
    }

    /**
     * Test for introduceParamLabels method.
     *
     * @return void
     */
    public function testIntroduceParamLabels()
    {
        $this->assertSame([], $this->model->introduceParamLabels());
    }
}

<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Fixtures;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute\Set;
use Magento\Framework\App\CacheInterface;
use Magento\Setup\Fixtures\EavVariationsFixture;
use Magento\Setup\Fixtures\FixtureModel;
use Magento\Store\Model\StoreManager;

class EavVariationsFixtureTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|FixtureModel
     */
    private $fixtureModelMock;

    /**
     * @var \Magento\Setup\Fixtures\EavVariationsFixture
     */
    private $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $attributeSetMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $cacheMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $eavConfigMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $attributeFactoryMock;

    public function setUp()
    {
        $this->fixtureModelMock = $this->getMock(FixtureModel::class, [], [], '', false);
        $this->eavConfigMock = $this->getMock(Config::class, [], [], '', false);
        $this->storeManagerMock = $this->getMock(StoreManager::class, [], [], '', false);
        $this->attributeSetMock = $this->getMock(Set::class, [], [], '', false);
        $this->cacheMock = $this->getMock(CacheInterface::class, [], [], '', false);
        $this->attributeFactoryMock = $this->getMock(AttributeFactory::class, ['create'], [], '', false);

        $this->model = (new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this))->getObject(
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

    public function testDoNotExecuteWhenAttributeAleadyExist()
    {
        $this->fixtureModelMock
            ->expects($this->any())
            ->method('getValue')
            ->with('configurable_products', [])
            ->willReturn(10);
        $this->eavConfigMock->expects($this->once())->method('getEntityAttributeCodes')
            ->willReturn(['configurable_variation']);
        $this->attributeFactoryMock->expects($this->never())->method('create');

        $this->model->execute();
    }

    public function testExecute()
    {
        $this->eavConfigMock->expects($this->once())->method('getEntityAttributeCodes')
            ->willReturn(['attr1', 'attr2']);
        $this->fixtureModelMock
            ->expects($this->any())
            ->method('getValue')
            ->willReturnMap([
                ['configurable_products', [], ['some-config']],
                ['configurable_products_variation', 3, 1],
            ]);

        $storeMock = $this->getMock(\Magento\Store\Model\Store::class, [], [], '', false);
        $this->storeManagerMock->expects($this->once())
            ->method('getStores')
            ->will($this->returnValue([$storeMock]));

        $this->attributeSetMock->expects($this->once())->method('load')->willReturnSelf();
        $this->attributeSetMock->expects($this->once())
            ->method('getDefaultGroupId')
            ->will($this->returnValue(2));

        $attributeMock = $this->getMockBuilder(\Magento\Catalog\Model\ResourceModel\Eav\Attribute::class)
            ->setMethods([
                'setAttributeSetId',
                'setAttributeGroupId',
                'save',
            ])->disableOriginalConstructor()
            ->getMock();
        $attributeMock->expects($this->exactly(2))
            ->method('setAttributeSetId')
            ->willReturnSelf();
        $attributeMock->expects($this->once())
            ->method('setAttributeGroupId')
            ->willReturnSelf();
        $this->attributeFactoryMock->expects($this->once())->method('create')
            ->with(
                [
                    'data' => [
                        'frontend_label' => [
                            'configurable variations',
                            'configurable variations',
                        ],
                        'frontend_input' => 'select',
                        'is_required' => '0',
                        'option' => [
                            'order' => ['option_1' => 1],
                            'value' => ['option_1' => ['option 1', 'option 1']],
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
        $this->cacheMock->expects($this->once())->method('remove')->with(Config::ATTRIBUTES_CACHE_ID . Product::ENTITY);

        $this->model->execute();
    }

    public function testGetActionTitle()
    {
        $this->assertSame('Generating configurable EAV variations', $this->model->getActionTitle());
    }

    public function testIntroduceParamLabels()
    {
        $this->assertSame([], $this->model->introduceParamLabels());
    }
}

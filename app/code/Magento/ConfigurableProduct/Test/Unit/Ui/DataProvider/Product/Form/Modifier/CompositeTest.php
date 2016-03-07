<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Test\Unit\Ui\DataProvider\Product\Form\Modifier;

use Magento\ConfigurableProduct\Ui\DataProvider\Product\Form\Modifier\Composite as CompositeModifier;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\ConfigurableProduct\Ui\DataProvider\Product\Form\Modifier\Data\AssociatedProducts;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableType;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;

class CompositeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var LocatorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productLocatorMock;

    /**
     * @var ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManagerMock;

    /**
     * @var AssociatedProducts|\PHPUnit_Framework_MockObject_MockObject
     */
    private $associatedProductsMock;

    /**
     * @var ProductInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productMock;

    protected function setUp()
    {
        $this->productLocatorMock = $this->getMockBuilder(LocatorInterface::class)
            ->getMockForAbstractClass();
        $this->objectManagerMock = $this->getMockBuilder(ObjectManagerInterface::class)
            ->getMockForAbstractClass();
        $this->associatedProductsMock = $this->getMockBuilder(AssociatedProducts::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->productMock = $this->getMockBuilder(ProductInterface::class)
            ->getMockForAbstractClass();

        $this->productLocatorMock->expects(static::any())
            ->method('getProduct')
            ->willReturn($this->productMock);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
    }

    public function testModifyData()
    {
        $productId = 'some_id';
        $productMatrix = ['product', 'matrix'];
        $productAttributesIds = ['product', 'attributes', 'ids'];
        $productAttributesCodes = ['product', 'attributes', 'codes'];
        $data = ['initial_data'];
        $result = [
            'initial_data',
            $productId => [
                'affect_configurable_product_attributes' => '1',
                'configurable-matrix' => $productMatrix,
                'attributes' => $productAttributesIds,
                'attribute_codes' => $productAttributesCodes
            ]
        ];

        $this->productMock->expects(static::any())
            ->method('getTypeId')
            ->willReturn(ConfigurableType::TYPE_CODE);
        $this->productMock->expects(static::any())
            ->method('getId')
            ->willReturn($productId);
        $this->associatedProductsMock->expects(static::any())
            ->method('getProductMatrix')
            ->willReturn($productMatrix);
        $this->associatedProductsMock->expects(static::any())
            ->method('getProductAttributesIds')
            ->willReturn($productAttributesIds);
        $this->associatedProductsMock->expects(static::any())
            ->method('getProductAttributesCodes')
            ->willReturn($productAttributesCodes);

        $this->assertSame($result, $this->createCompositeModifier()->modifyData($data));
    }

    public function testModifyMeta()
    {
        $initialMeta = ['initial_meta'];
        $resultMeta = ['result_meta'];
        $modifiers = ['modifier1', 'modifier2'];

        $this->productMock->expects(static::any())
            ->method('getTypeId')
            ->willReturn(ConfigurableType::TYPE_CODE);
        $this->objectManagerMock->expects(static::any())
            ->method('get')
            ->willReturnMap(
                $modifiersMap = [
                    ['modifier1', $this->createModifierMock($initialMeta, ['modifier1_meta'])],
                    ['modifier2', $this->createModifierMock(['modifier1_meta'], $resultMeta)]
                ]
            );

        $this->assertSame($resultMeta, $this->createCompositeModifier($modifiers)->modifyMeta($initialMeta));
    }

    /**
     * Create composite modifier
     *
     * @param array $modifiers
     * @return CompositeModifier
     */
    private function createCompositeModifier(array $modifiers = [])
    {
        return $this->objectManagerHelper->getObject(
            CompositeModifier::class,
            [
                'locator' => $this->productLocatorMock,
                'objectManager' => $this->objectManagerMock,
                'associatedProducts' => $this->associatedProductsMock,
                'modifiers' => $modifiers
            ]
        );
    }

    /**
     * Create modifier mock object
     *
     * @param array $initialMeta
     * @param array $resultMeta
     * @return ModifierInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createModifierMock(array $initialMeta, array $resultMeta)
    {
        $modifierMock = $this->getMockBuilder(ModifierInterface::class)
            ->getMockForAbstractClass();

        $modifierMock->expects(static::any())
            ->method('modifyMeta')
            ->with($initialMeta)
            ->willReturn($resultMeta);

        return $modifierMock;
    }
}

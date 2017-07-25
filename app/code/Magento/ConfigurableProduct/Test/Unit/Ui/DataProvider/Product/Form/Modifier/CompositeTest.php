<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
use Magento\Catalog\Ui\AllowedProductTypes;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
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

    /**
     * @var AllowedProductTypes|\PHPUnit_Framework_MockObject_MockObject
     */
    private $allowedProductTypesMock;

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
        $this->allowedProductTypesMock = $this->getMock(AllowedProductTypes::class, [], [], '', false);

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
                'attribute_codes' => $productAttributesCodes,
                'product' => [
                    'configurable_attributes_data' => null
                ]
            ]
        ];

        $this->productMock->expects(static::any())
            ->method('getTypeId')
            ->willReturn(ConfigurableType::TYPE_CODE);
        $this->allowedProductTypesMock->expects(static::once())
            ->method('isAllowedProductType')
            ->with($this->productMock)
            ->willReturn(true);
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

    public function testDisallowModifyData()
    {
        $data = ['some data'];
        $this->productMock->expects(static::any())
            ->method('getTypeId')
            ->willReturn(ConfigurableType::TYPE_CODE);
        $this->allowedProductTypesMock->expects(static::once())
            ->method('isAllowedProductType')
            ->with($this->productMock)
            ->willReturn(false);
        $this->productMock->expects(static::never())
            ->method('getId');
        $this->associatedProductsMock->expects(static::never())
            ->method('getProductMatrix');
        $this->associatedProductsMock->expects(static::never())
            ->method('getProductAttributesIds');
        $this->associatedProductsMock->expects(static::never())
            ->method('getProductAttributesCodes');

        $this->assertSame($data, $this->createCompositeModifier()->modifyData($data));
    }

    public function testModifyMeta()
    {
        $initialMeta = ['initial_meta'];
        $resultMeta = ['result_meta'];
        $modifiers = ['modifier1', 'modifier2'];

        $this->productMock->expects(static::any())
            ->method('getTypeId')
            ->willReturn(ConfigurableType::TYPE_CODE);
        $this->allowedProductTypesMock->expects(static::once())
            ->method('isAllowedProductType')
            ->with($this->productMock)
            ->willReturn(true);
        $this->objectManagerMock->expects(static::any())
            ->method('get')
            ->willReturnMap(
                [
                    ['modifier1', $this->createModifierMock($initialMeta, ['modifier1_meta'])],
                    ['modifier2', $this->createModifierMock(['modifier1_meta'], $resultMeta)]
                ]
            );

        $this->assertSame($resultMeta, $this->createCompositeModifier($modifiers)->modifyMeta($initialMeta));
    }

    public function testDisallowModifyMeta()
    {
        $meta = ['some meta'];
        $modifiers = ['modifier1', 'modifier2'];
        $this->productMock->expects(static::any())
            ->method('getTypeId')
            ->willReturn(ConfigurableType::TYPE_CODE);
        $this->allowedProductTypesMock->expects(static::once())
            ->method('isAllowedProductType')
            ->with($this->productMock)
            ->willReturn(false);
        $this->objectManagerMock->expects(static::never())
            ->method('get');

        $this->assertSame($meta, $this->createCompositeModifier($modifiers)->modifyMeta($meta));
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
                'allowedProductTypes' => $this->allowedProductTypesMock,
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

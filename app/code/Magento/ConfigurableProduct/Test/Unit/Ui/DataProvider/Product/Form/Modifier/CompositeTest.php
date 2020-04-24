<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Test\Unit\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Ui\AllowedProductTypes;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableType;
use Magento\ConfigurableProduct\Ui\DataProvider\Product\Form\Modifier\Composite as CompositeModifier;
use Magento\ConfigurableProduct\Ui\DataProvider\Product\Form\Modifier\Data\AssociatedProducts;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CompositeTest extends TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var LocatorInterface|MockObject
     */
    private $productLocatorMock;

    /**
     * @var ObjectManagerInterface|MockObject
     */
    private $objectManagerMock;

    /**
     * @var AssociatedProducts|MockObject
     */
    private $associatedProductsMock;

    /**
     * @var ProductInterface|MockObject
     */
    private $productMock;

    /**
     * @var AllowedProductTypes|MockObject
     */
    private $allowedProductTypesMock;

    protected function setUp(): void
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
        $this->allowedProductTypesMock = $this->createMock(AllowedProductTypes::class);

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
        $this->productMock->expects(self::any())
            ->method('getTypeId')
            ->willReturn(ConfigurableType::TYPE_CODE);
        $this->allowedProductTypesMock->expects(self::once())
            ->method('isAllowedProductType')
            ->with($this->productMock)
            ->willReturn(false);
        $this->objectManagerMock->expects(self::exactly(2))
            ->method('get')
            ->willReturnMap(
                [
                    ['modifier1', $this->createModifierMock($meta, ['modifier1_meta'])],
                    ['modifier2', $this->createModifierMock(['modifier1_meta'], $meta)],
                ]
            );

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
     * @return ModifierInterface|MockObject
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

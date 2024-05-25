<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Test\Unit\Model\Product\Validator;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Validator;
use Magento\Catalog\Model\ProductFactory;
use Magento\ConfigurableProduct\Model\Product\Validator\Plugin;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\App\Request\Http;
use Magento\Framework\DataObject;
use Magento\Framework\Event\Manager;
use Magento\Framework\Json\Helper\Data;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PluginTest extends TestCase
{
    /**
     * @var Plugin
     */
    protected $plugin;

    /**
     * @var MockObject
     */
    protected $eventManagerMock;

    /**
     * @var MockObject
     */
    protected $productFactoryMock;

    /**
     * @var MockObject
     */
    protected $jsonHelperMock;

    /**
     * @var Product|MockObject
     */
    protected $productMock;

    /**
     * @var Http|MockObject
     */
    protected $requestMock;

    /**
     * @var DataObject|MockObject
     */
    protected $responseMock;

    /**
     * @var array
     */
    protected $arguments;

    /**
     * @var array
     */
    protected $proceedResult = [1, 2, 3];

    /**
     * @var Validator|MockObject
     */
    protected $subjectMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->eventManagerMock = $this->createMock(Manager::class);
        $this->productFactoryMock = $this->createPartialMock(ProductFactory::class, ['create']);
        $this->jsonHelperMock = $this->createPartialMock(Data::class, ['jsonDecode']);
        $this->jsonHelperMock->expects($this->any())->method('jsonDecode')->willReturnArgument(0);
        $this->productMock = $this->createPartialMock(
            Product::class,
            ['getData', 'getAttributes', 'setTypeId']
        );
        $this->requestMock = $this->createPartialMock(
            Http::class,
            ['getPost', 'getParam', 'has']
        );
        $this->responseMock = $this->getMockBuilder(DataObject::class)
            ->addMethods(['setError', 'setMessage', 'setAttributes'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->arguments = [$this->productMock, $this->requestMock, $this->responseMock];

        $this->subjectMock = $this->createMock(Validator::class);
        $this->plugin = new Plugin(
            $this->eventManagerMock,
            $this->productFactoryMock,
            $this->jsonHelperMock
        );
    }

    /**
     * @return void
     */
    public function testBeforeValidate(): void
    {
        $this->requestMock->expects(static::once())->method('has')->with('attributes')->willReturn(true);
        $this->productMock->expects(static::once())->method('setTypeId')->willReturnSelf();

        $this->plugin->beforeValidate(
            $this->subjectMock,
            $this->productMock,
            $this->requestMock,
            $this->responseMock
        );
    }

    /**
     * @return void
     */
    public function testAfterValidateWithVariationsValid(): void
    {
        $matrix = ['products'];

        $plugin = $this->getMockBuilder(Plugin::class)
            ->onlyMethods(['_validateProductVariations'])
            ->setConstructorArgs(
                [
                    $this->eventManagerMock,
                    $this->productFactoryMock,
                    $this->jsonHelperMock
                ]
            )
            ->getMock();

        $plugin->expects($this->once())
            ->method('_validateProductVariations')
            ->with($this->productMock, $matrix, $this->requestMock)
            ->willReturn(null);

        $this->requestMock->expects($this->once())
            ->method('getPost')
            ->with('variations-matrix')
            ->willReturn($matrix);

        $this->responseMock->expects($this->never())->method('setError');

        $this->assertEquals(
            $this->proceedResult,
            $plugin->afterValidate(
                $this->subjectMock,
                $this->proceedResult,
                $this->productMock,
                $this->requestMock,
                $this->responseMock
            )
        );
    }

    /**
     * @return void
     */
    public function testAfterValidateWithVariationsInvalid(): void
    {
        $matrix = ['products'];

        $plugin = $this->getMockBuilder(Plugin::class)
            ->onlyMethods(['_validateProductVariations'])
            ->setConstructorArgs(
                [
                    $this->eventManagerMock,
                    $this->productFactoryMock,
                    $this->jsonHelperMock
                ]
            )
            ->getMock();

        $plugin->expects($this->once())
            ->method('_validateProductVariations')
            ->with($this->productMock, $matrix, $this->requestMock)
            ->willReturn(true);

        $this->requestMock->expects($this->once())
            ->method('getPost')
            ->with('variations-matrix')
            ->willReturn($matrix);

        $this->responseMock->expects($this->once())->method('setError')->with(true)->willReturnSelf();
        $this->responseMock->expects($this->once())->method('setMessage')->willReturnSelf();
        $this->responseMock->expects($this->once())->method('setAttributes')->willReturnSelf();
        $this->assertEquals(
            $this->proceedResult,
            $plugin->afterValidate(
                $this->subjectMock,
                $this->proceedResult,
                $this->productMock,
                $this->requestMock,
                $this->responseMock
            )
        );
    }

    /**
     * @return void
     */
    public function testAfterValidateIfVariationsNotExist(): void
    {
        $this->requestMock->expects($this->once())
            ->method('getPost')
            ->with('variations-matrix')
            ->willReturn(null);
        $this->eventManagerMock->expects($this->never())->method('dispatch');
        $this->plugin->afterValidate(
            $this->subjectMock,
            $this->proceedResult,
            $this->productMock,
            $this->requestMock,
            $this->responseMock
        );
    }

    /**
     * @return void
     */
    public function testAfterValidateWithVariationsAndRequiredAttributes(): void
    {
        $matrix = [
            ['data1', 'data2', 'configurable_attribute' => ['data1']],
            ['data3', 'data4', 'configurable_attribute' => ['data3']],
            ['data5', 'data6', 'configurable_attribute' => ['data5']]
        ];

        $this->productMock->expects($this->any())
            ->method('getData')
            ->willReturnMap(
                [
                    ['code1', null, 'value_code_1'],
                    ['code2', null, 'value_code_2'],
                    ['code3', null, 'value_code_3'],
                    ['code4', null, 'value_code_4'],
                    ['code5', null, 'value_code_5']
                ]
            );

        $this->requestMock->expects($this->once())
            ->method('getPost')
            ->with('variations-matrix')
            ->willReturn($matrix);

        $attribute1 = $this->createAttribute('code1', true, true);
        $attribute2 = $this->createAttribute('code2', true, false);
        $attribute3 = $this->createAttribute('code3', false, true);
        $attribute4 = $this->createAttribute('code4', false, false);
        $attribute5 = $this->createAttribute('code5', true, true);

        $attributes = [
            $attribute1,
            $attribute2,
            $attribute3,
            $attribute4,
            $attribute5
        ];

        $requiredAttributes = [
            'code1' => 'value_code_1',
            'code5' => 'value_code_5'
        ];

        $product1 = $this->createProduct();
        $product1
            ->method('addData')
            ->willReturnCallback(function ($arg1) use ($requiredAttributes, $matrix, $product1) {
                if ($arg1 == $requiredAttributes || $arg1 == $matrix[0]) {
                    return $product1;
                }
            });

        $product2 = $this->createProduct();
        $product2
            ->method('addData')
            ->willReturnCallback(function ($arg1) use ($requiredAttributes, $matrix, $product2) {
                if ($arg1 == $requiredAttributes || $arg1 == $matrix[2]) {
                    return $product2;
                }
            });

        $product3 = $this->createProduct();
        $product3
            ->method('addData')
            ->willReturnCallback(function ($arg1) use ($requiredAttributes, $matrix, $product3) {
                if ($arg1 == $requiredAttributes || $arg1 == $matrix[2]) {
                    return $product3;
                }
            });

        $this->productMock->expects($this->exactly(3))
            ->method('getAttributes')
            ->willReturn($attributes);

        $this->responseMock->expects($this->never())->method('setError');

        $result = $this->plugin->afterValidate(
            $this->subjectMock,
            $this->proceedResult,
            $this->productMock,
            $this->requestMock,
            $this->responseMock
        );
        $this->assertEquals($this->proceedResult, $result);
    }

    /**
     * @return MockObject|Product
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @internal param array $attributes
     */
    private function createProduct(): Product
    {
        $productMock = $this->createPartialMock(
            Product::class,
            ['getAttributes', 'addData', 'setAttributeSetId', 'validate']
        );

        $this->productFactoryMock
            ->method('create')
            ->willReturn($productMock);

        $productMock->expects($this->any())
            ->method('validate')
            ->willReturn(true);

        return $productMock;
    }

    /**
     * @param $attributeCode
     * @param $isUserDefined
     * @param $isRequired
     *
     * @return MockObject|AbstractAttribute
     */
    private function createAttribute(
        $attributeCode,
        $isUserDefined,
        $isRequired
    ): AbstractAttribute {
        $attribute = $this->getMockBuilder(AbstractAttribute::class)
            ->disableOriginalConstructor()
            ->onlyMethods(
                [
                    'getAttributeCode',
                    'getIsUserDefined',
                    'getIsRequired'
                ]
            )
            ->getMock();
        $attribute->expects($this->any())
            ->method('getAttributeCode')
            ->willReturn($attributeCode);
        $attribute->expects($this->any())
            ->method('getIsRequired')
            ->willReturn($isRequired);
        $attribute->expects($this->any())
            ->method('getIsUserDefined')
            ->willReturn($isUserDefined);

        return $attribute;
    }
}

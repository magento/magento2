<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Test\Unit\Model\Product\Validator;

class PluginTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\ConfigurableProduct\Model\Product\Validator\Plugin
     */
    protected $plugin;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $eventManagerMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $productFactoryMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $jsonHelperMock;

    /**
     * @var \Magento\Catalog\Model\Product|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $productMock;

    /**
     * @var \Magento\Framework\App\Request\Http|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $requestMock;

    /**
     * @var \Magento\Framework\DataObject|\PHPUnit\Framework\MockObject\MockObject
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
     * @var \Magento\Catalog\Model\Product\Validator|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $subjectMock;

    protected function setUp(): void
    {
        $this->eventManagerMock = $this->createMock(\Magento\Framework\Event\Manager::class);
        $this->productFactoryMock = $this->createPartialMock(\Magento\Catalog\Model\ProductFactory::class, ['create']);
        $this->jsonHelperMock = $this->createPartialMock(\Magento\Framework\Json\Helper\Data::class, ['jsonDecode']);
        $this->jsonHelperMock->expects($this->any())->method('jsonDecode')->willReturnArgument(0);
        $this->productMock = $this->createPartialMock(
            \Magento\Catalog\Model\Product::class,
            ['getData', 'getAttributes', 'setTypeId']
        );
        $this->requestMock = $this->createPartialMock(
            \Magento\Framework\App\Request\Http::class,
            ['getPost', 'getParam', '__wakeup', 'has']
        );
        $this->responseMock = $this->createPartialMock(
            \Magento\Framework\DataObject::class,
            ['setError', 'setMessage', 'setAttributes']
        );
        $this->arguments = [$this->productMock, $this->requestMock, $this->responseMock];

        $this->subjectMock = $this->createMock(\Magento\Catalog\Model\Product\Validator::class);
        $this->plugin = new \Magento\ConfigurableProduct\Model\Product\Validator\Plugin(
            $this->eventManagerMock,
            $this->productFactoryMock,
            $this->jsonHelperMock
        );
    }

    public function testBeforeValidate()
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

    public function testAfterValidateWithVariationsValid()
    {
        $matrix = ['products'];

        $plugin = $this->getMockBuilder(\Magento\ConfigurableProduct\Model\Product\Validator\Plugin::class)
            ->setMethods(['_validateProductVariations'])
            ->setConstructorArgs([$this->eventManagerMock, $this->productFactoryMock, $this->jsonHelperMock])
            ->getMock();

        $plugin->expects(
            $this->once()
        )->method(
            '_validateProductVariations'
        )->with(
            $this->productMock,
            $matrix,
            $this->requestMock
        )->willReturn(
            null
        );

        $this->requestMock->expects(
            $this->once()
        )->method(
            'getPost'
        )->with(
            'variations-matrix'
        )->willReturn(
            $matrix
        );

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

    public function testAfterValidateWithVariationsInvalid()
    {
        $matrix = ['products'];

        $plugin = $this->getMockBuilder(\Magento\ConfigurableProduct\Model\Product\Validator\Plugin::class)
            ->setMethods(['_validateProductVariations'])
            ->setConstructorArgs([$this->eventManagerMock, $this->productFactoryMock, $this->jsonHelperMock])
            ->getMock();

        $plugin->expects(
            $this->once()
        )->method(
            '_validateProductVariations'
        )->with(
            $this->productMock,
            $matrix,
            $this->requestMock
        )->willReturn(
            true
        );

        $this->requestMock->expects(
            $this->once()
        )->method(
            'getPost'
        )->with(
            'variations-matrix'
        )->willReturn(
            $matrix
        );

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

    public function testAfterValidateIfVariationsNotExist()
    {
        $this->requestMock->expects(
            $this->once()
        )->method(
            'getPost'
        )->with(
            'variations-matrix'
        )->willReturn(
            null
        );
        $this->eventManagerMock->expects($this->never())->method('dispatch');
        $this->plugin->afterValidate(
            $this->subjectMock,
            $this->proceedResult,
            $this->productMock,
            $this->requestMock,
            $this->responseMock
        );
    }

    public function testAfterValidateWithVariationsAndRequiredAttributes()
    {
        $matrix = [
            ['data1', 'data2', 'configurable_attribute' => ['data1']],
            ['data3', 'data4', 'configurable_attribute' => ['data3']],
            ['data5', 'data6', 'configurable_attribute' => ['data5']],
        ];

        $this->productMock->expects($this->any())
            ->method('getData')
            ->willReturnMap(
                
                    [
                        ['code1', null, 'value_code_1'],
                        ['code2', null, 'value_code_2'],
                        ['code3', null, 'value_code_3'],
                        ['code4', null, 'value_code_4'],
                        ['code5', null, 'value_code_5'],
                    ]
                
            );

        $this->requestMock->expects(
            $this->once()
        )->method(
            'getPost'
        )->with(
            'variations-matrix'
        )->willReturn(
            $matrix
        );

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
            $attribute5,
        ];

        $requiredAttributes = [
            'code1' => 'value_code_1',
            'code5' => 'value_code_5',
        ];

        $product1 = $this->createProduct(0, 1);
        $product1->expects($this->at(1))
            ->method('addData')
            ->with($requiredAttributes)
            ->willReturnSelf();
        $product1->expects($this->at(2))
            ->method('addData')
            ->with($matrix[0])
            ->willReturnSelf();
        $product2 = $this->createProduct(1, 2);
        $product2->expects($this->at(1))
            ->method('addData')
            ->with($requiredAttributes)
            ->willReturnSelf();
        $product2->expects($this->at(2))
            ->method('addData')
            ->with($matrix[1])
            ->willReturnSelf();
        $product3 = $this->createProduct(2, 3);
        $product3->expects($this->at(1))
            ->method('addData')
            ->with($requiredAttributes)
            ->willReturnSelf();
        $product3->expects($this->at(2))
            ->method('addData')
            ->with($matrix[2])
            ->willReturnSelf();

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
        $this->assertEquals(
            $this->proceedResult,
            $result
        );
    }

    /**
     * @param $index
     * @param $id
     * @param bool $isValid
     * @internal param array $attributes
     * @return \PHPUnit\Framework\MockObject\MockObject|\Magento\Catalog\Model\Product
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    private function createProduct($index, $id, $isValid = true)
    {
        $productMock = $this->createPartialMock(
            \Magento\Catalog\Model\Product::class,
            ['getAttributes', 'addData', 'setAttributeSetId', 'validate']
        );
        $this->productFactoryMock->expects($this->at($index))
            ->method('create')
            ->willReturn($productMock);
        $productMock->expects($this->once())
            ->method('validate')
            ->willReturn($isValid);

        return $productMock;
    }

    /**
     * @param $attributeCode
     * @param $isUserDefined
     * @param $isRequired
     * @return \PHPUnit\Framework\MockObject\MockObject|\Magento\Eav\Model\Entity\Attribute\AbstractAttribute
     */
    private function createAttribute($attributeCode, $isUserDefined, $isRequired)
    {
        $attribute = $this->getMockBuilder(\Magento\Eav\Model\Entity\Attribute\AbstractAttribute::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAttributeCode', 'getIsUserDefined', 'getIsRequired'])
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

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Test\Unit\Model\Product\Validator;

class PluginTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\ConfigurableProduct\Model\Product\Validator\Plugin
     */
    protected $plugin;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $jsonHelperMock;

    /**
     * @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productMock;

    /**
     * @var \Magento\Framework\App\Request\Http|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * @var \Magento\Framework\DataObject|\PHPUnit_Framework_MockObject_MockObject
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
     * @var \Magento\Catalog\Model\Product\Validator|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $subjectMock;

    protected function setUp()
    {
        $this->eventManagerMock = $this->getMock(\Magento\Framework\Event\Manager::class, [], [], '', false);
        $this->productFactoryMock = $this->getMock(
            \Magento\Catalog\Model\ProductFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->jsonHelperMock = $this->getMock(
            \Magento\Framework\Json\Helper\Data::class,
            ['jsonDecode'],
            [],
            '',
            false
        );
        $this->jsonHelperMock->expects($this->any())->method('jsonDecode')->will($this->returnArgument(0));
        $this->productMock = $this->getMock(
            \Magento\Catalog\Model\Product::class,
            ['getData', 'getAttributes', 'setTypeId'],
            [],
            '',
            false
        );
        $this->requestMock = $this->getMock(
            \Magento\Framework\App\Request\Http::class,
            ['getPost', 'getParam', '__wakeup', 'has'],
            [],
            '',
            false
        );
        $this->responseMock = $this->getMock(
            \Magento\Framework\DataObject::class,
            ['setError', 'setMessage', 'setAttributes'],
            [],
            '',
            false
        );
        $this->arguments = [$this->productMock, $this->requestMock, $this->responseMock];

        $this->subjectMock = $this->getMock(\Magento\Catalog\Model\Product\Validator::class, [], [], '', false);
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

        $plugin = $this->getMock(
            \Magento\ConfigurableProduct\Model\Product\Validator\Plugin::class,
            ['_validateProductVariations'],
            [$this->eventManagerMock, $this->productFactoryMock, $this->jsonHelperMock]
        );

        $plugin->expects(
            $this->once()
        )->method(
            '_validateProductVariations'
        )->with(
            $this->productMock,
            $matrix,
            $this->requestMock
        )->will(
            $this->returnValue(null)
        );

        $this->requestMock->expects(
            $this->once()
        )->method(
            'getPost'
        )->with(
            'variations-matrix'
        )->will(
            $this->returnValue($matrix)
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

        $plugin = $this->getMock(
            \Magento\ConfigurableProduct\Model\Product\Validator\Plugin::class,
            ['_validateProductVariations'],
            [$this->eventManagerMock, $this->productFactoryMock, $this->jsonHelperMock]
        );

        $plugin->expects(
            $this->once()
        )->method(
            '_validateProductVariations'
        )->with(
            $this->productMock,
            $matrix,
            $this->requestMock
        )->will(
            $this->returnValue(true)
        );

        $this->requestMock->expects(
            $this->once()
        )->method(
            'getPost'
        )->with(
            'variations-matrix'
        )->will(
            $this->returnValue($matrix)
        );

        $this->responseMock->expects($this->once())->method('setError')->with(true)->will($this->returnSelf());
        $this->responseMock->expects($this->once())->method('setMessage')->will($this->returnSelf());
        $this->responseMock->expects($this->once())->method('setAttributes')->will($this->returnSelf());
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
        )->will(
            $this->returnValue(null)
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
            ->will(
                $this->returnValueMap(
                    [
                        ['code1', null, 'value_code_1'],
                        ['code2', null, 'value_code_2'],
                        ['code3', null, 'value_code_3'],
                        ['code4', null, 'value_code_4'],
                        ['code5', null, 'value_code_5'],
                    ]
                )
            );

        $this->requestMock->expects(
            $this->once()
        )->method(
            'getPost'
        )->with(
            'variations-matrix'
        )->will(
            $this->returnValue($matrix)
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
            ->will($this->returnSelf());
        $product1->expects($this->at(2))
            ->method('addData')
            ->with($matrix[0])
            ->will($this->returnSelf());
        $product2 = $this->createProduct(1, 2);
        $product2->expects($this->at(1))
            ->method('addData')
            ->with($requiredAttributes)
            ->will($this->returnSelf());
        $product2->expects($this->at(2))
            ->method('addData')
            ->with($matrix[1])
            ->will($this->returnSelf());
        $product3 = $this->createProduct(2, 3);
        $product3->expects($this->at(1))
            ->method('addData')
            ->with($requiredAttributes)
            ->will($this->returnSelf());
        $product3->expects($this->at(2))
            ->method('addData')
            ->with($matrix[2])
            ->will($this->returnSelf());

        $this->productMock->expects($this->exactly(3))
            ->method('getAttributes')
            ->will($this->returnValue($attributes));

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
     * @return \PHPUnit_Framework_MockObject_MockObject|\Magento\Catalog\Model\Product
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    private function createProduct($index, $id, $isValid = true)
    {
        $productMock = $this->getMock(
            \Magento\Catalog\Model\Product::class,
            ['getAttributes', 'addData', 'setAttributeSetId', 'validate'],
            [],
            '',
            false
        );
        $this->productFactoryMock->expects($this->at($index))
            ->method('create')
            ->will($this->returnValue($productMock));
        $productMock->expects($this->once())
            ->method('validate')
            ->will($this->returnValue($isValid));

        return $productMock;
    }

    /**
     * @param $attributeCode
     * @param $isUserDefined
     * @param $isRequired
     * @return \PHPUnit_Framework_MockObject_MockObject|\Magento\Eav\Model\Entity\Attribute\AbstractAttribute
     */
    private function createAttribute($attributeCode, $isUserDefined, $isRequired)
    {
        $attribute = $this->getMockBuilder(\Magento\Eav\Model\Entity\Attribute\AbstractAttribute::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAttributeCode', 'getIsUserDefined', 'getIsRequired'])
            ->getMock();
        $attribute->expects($this->any())
            ->method('getAttributeCode')
            ->will($this->returnValue($attributeCode));
        $attribute->expects($this->any())
            ->method('getIsRequired')
            ->will($this->returnValue($isRequired));
        $attribute->expects($this->any())
            ->method('getIsUserDefined')
            ->will($this->returnValue($isUserDefined));

        return $attribute;
    }
}

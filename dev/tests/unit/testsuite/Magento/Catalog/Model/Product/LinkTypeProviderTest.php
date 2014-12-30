<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Catalog\Model\Product;

class LinkTypeProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\Product\LinkTypeProvider
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $linkTypeBuilderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $linkAttributeBuilderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $linkFactoryMock;

    /**
     * @var Array
     */
    protected $linkTypes;

    protected function setUp()
    {
        $this->linkTypeBuilderMock = $this->getMock(
            'Magento\Catalog\Api\Data\ProductLinkTypeDataBuilder',
            ['create', 'populateWithArray']
        );
        $this->linkAttributeBuilderMock = $this->getMock(
            'Magento\Catalog\Api\Data\ProductLinkAttributeDataBuilder',
            ['populateWithArray', 'create'], [], '', false, false
        );
        $this->linkFactoryMock = $this->getMock(
            '\Magento\Catalog\Model\Product\LinkFactory',
            ['create'], [], '', false, false
        );
        $this->linkTypes = [
            'test_product_link_1' => 'test_code_1',
            'test_product_link_2' => 'test_code_2',
            'test_product_link_3' => 'test_code_3',
        ];
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->model = $objectManager->getObject(
            'Magento\Catalog\Model\Product\LinkTypeProvider',
            [
                'linkTypeBuilder' => $this->linkTypeBuilderMock,
                'linkAttributeBuilder' => $this->linkAttributeBuilderMock,
                'linkFactory' => $this->linkFactoryMock,
                'linkTypes' => $this->linkTypes
            ]
        );
    }

    public function testGetItems()
    {
        $expectedResult = [];
        $objectMocks = [];
        foreach ($this->linkTypes as $type => $typeCode) {
            $value = ['name' => $type, 'code' => $typeCode];
            $objectMock = $this->getMock('\Magento\Framework\Object', ['create'], [], '', false);
            $objectMock->expects($this->once())->method('create')->willReturn($value);
            $objectMocks[] = $objectMock;
            $expectedResult[] = $value;
        }
        $valueMap = function ($expectedResult, $objectMocks) {
            $output = [];
            foreach ($expectedResult as $key => $result) {
                $output[] = [$expectedResult[$key], $objectMocks[$key]];
            }
            return $output;
        };
        $this->linkTypeBuilderMock->expects($this->exactly(3))->method('populateWithArray')->will($this->returnValueMap(
            $valueMap($expectedResult, $objectMocks)
        ));
        $this->assertEquals($expectedResult, $this->model->getItems());
    }

    /**
     * @dataProvider getItemAttributesDataProvider
     */
    public function testGetItemAttributes($type, $typeId)
    {
        $attributes = [
            ['code' => 'test_code_1', 'type' => 'test_type_1'],
        ];
        $expectedResult = [
            ['attribute_code' => $attributes[0]['code'], 'value' => $attributes[0]['type']],
        ];
        $objectMock = $this->getMock('\Magento\Framework\Object', ['create'], [], '', false);
        $objectMock->expects($this->once())->method('create')->willReturn(
            ['attribute_code' => $attributes[0]['code'], 'value' => $attributes[0]['type']]
        );
        $linkMock = $this->getMock('\Magento\Catalog\Model\Product\Link', ['getAttributes'], [], '', false);
        $linkMock->expects($this->once())->method('getAttributes')->willReturn($attributes);
        $this->linkFactoryMock->expects($this->once())->method('create')->with($typeId)->willReturn($linkMock);
        $this->linkAttributeBuilderMock->expects($this->once())->method('populateWithArray')->willReturn($objectMock);
        $this->assertEquals($expectedResult, $this->model->getItemAttributes($type));
    }

    public function getItemAttributesDataProvider()
    {
        return [
            ['test_product_link_2', ['data' => ['link_type_id' => 'test_code_2']]],
            ['null_product', ['data' => ['link_type_id' => null]]]
        ];
    }
}

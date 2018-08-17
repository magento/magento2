<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Test\Unit\Model\Entity;

class AttributeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Attribute model to be tested
     * @var \Magento\Eav\Model\Entity\Attribute|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = $this->createPartialMock(\Magento\Eav\Model\Entity\Attribute::class, ['__wakeup']);
    }

    protected function tearDown()
    {
        $this->_model = null;
    }

    /**
     * @param string $givenFrontendInput
     * @param string $expectedBackendType
     * @dataProvider dataGetBackendTypeByInput
     */
    public function testGetBackendTypeByInput($givenFrontendInput, $expectedBackendType)
    {
        $this->assertEquals($expectedBackendType, $this->_model->getBackendTypeByInput($givenFrontendInput));
    }

    /**
     * @return array
     */
    public static function dataGetBackendTypeByInput()
    {
        return [
            ['unrecognized-frontend-input', null],
            ['text', 'varchar'],
            ['gallery', 'varchar'],
            ['media_image', 'varchar'],
            ['multiselect', 'varchar'],
            ['image', 'text'],
            ['textarea', 'text'],
            ['date', 'datetime'],
            ['select', 'int'],
            ['boolean', 'int'],
            ['price', 'decimal'],
            ['weight', 'decimal']
        ];
    }

    /**
     * @param string $givenFrontendInput
     * @param string $expectedDefaultValue
     * @dataProvider dataGetDefaultValueByInput
     */
    public function testGetDefaultValueByInput($givenFrontendInput, $expectedDefaultValue)
    {
        $this->assertEquals($expectedDefaultValue, $this->_model->getDefaultValueByInput($givenFrontendInput));
    }

    /**
     * @return array
     */
    public static function dataGetDefaultValueByInput()
    {
        return [
            ['unrecognized-frontend-input', ''],
            ['select', ''],
            ['gallery', ''],
            ['media_image', ''],
            ['multiselect', null],
            ['text', 'default_value_text'],
            ['price', 'default_value_text'],
            ['image', 'default_value_text'],
            ['weight', 'default_value_text'],
            ['textarea', 'default_value_textarea'],
            ['date', 'default_value_date'],
            ['boolean', 'default_value_yesno']
        ];
    }

    /**
     * @param array|null $sortWeights
     * @param float $expected
     * @dataProvider getSortWeightDataProvider
     */
    public function testGetSortWeight($sortWeights, $expected)
    {
        $setId = 123;
        $this->_model->setAttributeSetInfo([$setId => $sortWeights]);
        $this->assertEquals($expected, $this->_model->getSortWeight($setId));
    }

    /**
     * @return array
     */
    public function getSortWeightDataProvider()
    {
        return [
            'empty set info' => ['sortWeights' => null, 'expectedWeight' => 0],
            'no group sort' => ['sortWeights' => ['sort' => 5], 'expectedWeight' => 0.0005],
            'no sort' => ['sortWeights' => ['group_sort' => 7], 'expectedWeight' => 7000],
            'group sort and sort' => [
                'sortWeights' => ['group_sort' => 7, 'sort' => 5],
                'expectedWeight' => 7000.0005,
            ]
        ];
    }

    public function testGetFrontendLabels()
    {
        $attributeId = 1;
        $storeLabels = ['test_attribute_store1'];
        $frontendLabelFactory = $this->getMockBuilder(\Magento\Eav\Model\Entity\Attribute\FrontendLabelFactory::class)
            ->setMethods(['create'])
            ->getMock();
        $resource = $this->getMockBuilder(\Magento\Eav\Model\ResourceModel\Entity\Attribute::class)
            ->setMethods(['getStoreLabelsByAttributeId'])
            ->disableOriginalConstructor()
            ->getMock();
        $arguments = [
            '_resource' => $resource,
            'frontendLabelFactory' => $frontendLabelFactory,
        ];
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_model = $objectManager->getObject(\Magento\Eav\Model\Entity\Attribute::class, $arguments);
        $this->_model->setAttributeId($attributeId);

        $resource->expects($this->once())
            ->method('getStoreLabelsByAttributeId')
            ->with($attributeId)
            ->willReturn($storeLabels);
        $frontendLabel = $this->getMockBuilder(\Magento\Eav\Model\Entity\Attribute\FrontendLabel::class)
            ->setMethods(['setStoreId', 'setLabel'])
            ->disableOriginalConstructor()
            ->getMock();
        $frontendLabelFactory->expects($this->once())
            ->method('create')
            ->willReturn($frontendLabel);
        $expectedFrontendLabel[] = $frontendLabel;

        $this->assertEquals($expectedFrontendLabel, $this->_model->getFrontendLabels());
    }
}

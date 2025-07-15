<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Eav\Test\Unit\Model\Entity;

use Magento\Eav\Model\Entity\Attribute;
use Magento\Eav\Model\Entity\Attribute\FrontendLabel;
use Magento\Eav\Model\Entity\Attribute\FrontendLabelFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for EAV Entity attribute model
 */
class AttributeTest extends TestCase
{
    /**
     * Attribute model to be tested
     * @var Attribute|MockObject
     */
    protected $_model;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->_model = $this->createPartialMock(Attribute::class, ['__wakeup']);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $this->_model = null;
    }

    /**
     * @param string $givenFrontendInput
     * @param string $expectedBackendType
     * @dataProvider dataGetBackendTypeByInput
     * @return void
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
            ['multiselect', 'text'],
            ['image', 'text'],
            ['textarea', 'text'],
            ['date', 'datetime'],
            ['datetime', 'datetime'],
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
            ['datetime', 'default_value_datetime'],
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
    public static function getSortWeightDataProvider()
    {
        return [
            'empty set info' => ['sortWeights' => null, 'expected' => 0],
            'no group sort' => ['sortWeights' => ['sort' => 5], 'expected' => 0.0005],
            'no sort' => ['sortWeights' => ['group_sort' => 7], 'expected' => 7000],
            'group sort and sort' => [
                'sortWeights' => ['group_sort' => 7, 'sort' => 5],
                'expected' => 7000.0005,
            ]
        ];
    }

    /**
     * return void
     */
    public function testGetFrontendLabels()
    {
        $attributeId = 1;
        $storeLabels = ['test_attribute_store1'];
        $frontendLabelFactory = $this->getMockBuilder(FrontendLabelFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $resource = $this->getMockBuilder(\Magento\Eav\Model\ResourceModel\Entity\Attribute::class)
            ->onlyMethods(['getStoreLabelsByAttributeId'])
            ->disableOriginalConstructor()
            ->getMock();
        $arguments = [
            '_resource' => $resource,
            'frontendLabelFactory' => $frontendLabelFactory,
        ];
        $objectManager = new ObjectManager($this);
        $this->_model = $objectManager->getObject(Attribute::class, $arguments);
        $this->_model->setAttributeId($attributeId);

        $resource->expects($this->once())
            ->method('getStoreLabelsByAttributeId')
            ->with($attributeId)
            ->willReturn($storeLabels);
        $frontendLabel = $this->getMockBuilder(FrontendLabel::class)
            ->onlyMethods(['setStoreId', 'setLabel'])
            ->disableOriginalConstructor()
            ->getMock();
        $frontendLabelFactory->expects($this->once())
            ->method('create')
            ->willReturn($frontendLabel);
        $expectedFrontendLabel[] = $frontendLabel;

        $this->assertEquals($expectedFrontendLabel, $this->_model->getFrontendLabels());
    }
}

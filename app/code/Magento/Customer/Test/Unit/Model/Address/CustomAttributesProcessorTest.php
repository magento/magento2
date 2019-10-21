<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Model\Address;

use Magento\Customer\Api\AddressMetadataInterface;
use Magento\Eav\Model\Config;
use Magento\Eav\Api\AttributeOptionManagementInterface;
use Magento\Customer\Model\Address\CustomAttributesProcessor;
use Magento\Customer\Api\Data\AttributeMetadataInterface;
use Magento\Customer\Model\Attribute as CustomerAttribute;

/**
 * Unit test for CustomAttributesProcessorTest.
 */
class CustomAttributesProcessorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var array
     */
    private $testData = [
        [
            'attribute_code' => 'attribute1',
            'visible' => false,
            'used_in_forms' => ['customer_address_edit']
        ],
        [
            'attribute_code' => 'attribute2',
            'visible' => true,
            'used_in_forms' => ['adminhtml_customer_address']
        ],
        [
            'attribute_code' => 'attribute3',
            'visible' => true,
            'used_in_forms' => ['customer_address_edit']
        ]
    ];

    /**
     * @var CustomAttributesProcessor
     */
    private $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $addressMetadata;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $attributeOptionManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $eavConfig;

    /**
     * Init Mock Objects
     */
    protected function setUp(): void
    {
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->addressMetadata = $this->getMockBuilder(AddressMetadataInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->attributeOptionManager = $this->getMockBuilder(AttributeOptionManagementInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->eavConfig = $this->createEavConfigMock($this->testData);

        $this->addressMetadata
            ->expects($this->once())
            ->method('getAllAttributesMetadata')
            ->willReturn($this->getAttributesMocks($this->testData));

        $this->model = $objectManagerHelper->getObject(
            CustomAttributesProcessor::class,
            [
                'addressMetadata' => $this->addressMetadata,
                'attributeOptionManager' => $this->attributeOptionManager,
                'eavConfig' => $this->eavConfig
            ]
        );
    }

    /**
     * Test Filter Not Visible Attributes
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function testFilterNotVisibleAttributes(): void
    {
        $initialData = [
            'attribute1' => [
                'attribute_code' => 'attribute1',
                'value' => 'attribute1test'
            ],
            'attribute2' => [
                'attribute_code' => 'attribute2',
                'value' => 'attribute2test'
            ],
            'attribute3' => [
                'attribute_code' => 'attribute3',
                'value' => 'attribute3test'
            ]
        ];

        $expectedData = [
            'attribute3' => [
                'attribute_code' => 'attribute3',
                'value' => 'attribute3test'
            ]
        ];

        $this->assertEquals($expectedData, $this->model->filterNotVisibleAttributes($initialData));
    }

    /**
     * Get Attributes Mock
     *
     * @param array $options
     * @return array
     */
    protected function getAttributesMocks(array $options): array
    {
        $attrsMocks = [];

        foreach ($options as $attr) {
            $attrMock = $this->getMockBuilder(AttributeMetadataInterface::class)
                ->disableOriginalConstructor()
                ->getMock();

            $attrMock->expects($this->any())
                ->method('isVisible')
                ->willReturn($attr['visible']);

            $attrMock->expects($this->any())
                ->method('getAttributeCode')
                ->willReturn($attr['attribute_code']);

            $attrsMocks[] = $attrMock;
        }

        return $attrsMocks;
    }

    /**
     * Create Eav Config Mock
     *
     * @param array $options
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    protected function createEavConfigMock(array $options)
    {
        $index = 0;
        $eavConfig = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();

        foreach ($options as $attr) {
            if ($attr['visible']) {
                $customerAttr = $this->getMockBuilder(CustomerAttribute::class)
                    ->disableOriginalConstructor()
                    ->getMock();

                $customerAttr->expects($this->once())
                    ->method('getUsedInForms')
                    ->willReturn($attr['used_in_forms']);

                $eavConfig->expects($this->at($index++))
                    ->method('getAttribute')
                    ->with('customer_address', $attr['attribute_code'])
                    ->willReturn($customerAttr);
            }
        }

        return $eavConfig;
    }
}

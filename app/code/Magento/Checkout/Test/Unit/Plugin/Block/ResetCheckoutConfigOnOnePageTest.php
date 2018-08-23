<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Checkout\Test\Unit\Plugin\Block;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class ResetCheckoutConfigOnOnePageTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @var \Magento\Checkout\Plugin\Block\ResetCheckoutConfigOnOnePage
     */
    private $resetCheckoutConfigOnOnePage;

    /**
     * @var \Magento\Eav\Api\AttributeOptionManagementInterface
     */
    private $attributeOptionManagerMock;

    /**
    * @var \Magento\Framework\Serialize\SerializerInterface
    */
    private $serializerMock;

    /**
    * @var \Magento\Checkout\Block\Onepage
    */
    private $onePageMock;

    /**
    * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
    */
    private $objectManagerHelper;

    protected function setUp()
    {

        $this->attributeOptionManagerMock = $this->createMock(
            \Magento\Eav\Api\AttributeOptionManagementInterface::class
        );

        $this->serializerMock = $this->createMock(
            \Magento\Framework\Serialize\SerializerInterface::class
        );

        $this->onePageMock = $this->createMock(\Magento\Checkout\Block\Onepage::class);

        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->resetCheckoutConfigOnOnePage = $this->objectManagerHelper->getObject(
            \Magento\Checkout\Plugin\Block\ResetCheckoutConfigOnOnePage::class,
            [
                'attributeOptionManager' => $this->attributeOptionManagerMock,
                'serializer' => $this->serializerMock
            ]
        );
    }

    /**
     *  Test for reformat serialized checkout config with empty Result for Onepage
     *
     * @covers \Magento\Checkout\Plugin\Block\ResetCheckoutConfigOnOnePage::afterGetSerializedCheckoutConfig()
     * @return void
     */
    public function testAfterGetSerializedCheckoutConfigWithEmptyResults()
    {
        $result = $this->resetCheckoutConfigOnOnePage->afterGetSerializedCheckoutConfig(
            $this->onePageMock, json_encode([])
        );

        $this->assertEquals(
            $result,
            '[]'
        );
    }

    /**
     *  Test for reformat serialized checkout config with only options custom attributes in custom address for Onepage
     *
     * @covers \Magento\Checkout\Plugin\Block\ResetCheckoutConfigOnOnePage::afterGetSerializedCheckoutConfig()
     * @return void
     */
    public function testAfterGetSerializedCheckoutConfigWithOnlyOptionsCustomAttributesInCustomAddressResults()
    {
        $textAttributeCode = 'text';
        $textAttributeValue = 'some text';
        $dropAttributeCode = 'dropnew';
        $dropAttributeValue1 = 15;
        $dropAttributeLabel1 = 'drop 1';
        $dropAttributeValue2 = 16;
        $dropAttributeLabel2 = 'drop 2';
        $multiDropAttributeValue1 = 17;
        $multiDropAttributeLabel1 = 'multidrop 1';
        $multiDropAttributeValue2 = 18;
        $multiDropAttributeLabel2 = 'multidrop 2';
        $multiDropAttributeCode = 'multidrop';
        $mockCheckoutConfig = [
            'customerData' => [
                'addresses' => [
                    [
                        'custom_attributes' => [
                            $dropAttributeCode => [
                                'attribute_code' => $dropAttributeCode,
                                'value' => "$dropAttributeValue1",
                            ],
                            $textAttributeCode => [
                                'attribute_code' => $textAttributeCode,
                                'value' => $textAttributeValue,
                            ],
                            $multiDropAttributeCode => [
                                'attribute_code' => $multiDropAttributeCode,
                                'value' => "$multiDropAttributeValue1,$multiDropAttributeValue2",
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $expectedCheckoutConfig = [
            'customerData' => [
                'addresses' => [
                    [
                        'custom_attributes' => [
                            $dropAttributeCode => [
                                'attribute_code' => $dropAttributeCode,
                                'value' => $dropAttributeValue1,
                                'label' => $dropAttributeLabel1
                            ],
                            $textAttributeCode => [
                                'attribute_code' => $textAttributeCode,
                                'value' => $textAttributeValue,
                            ],
                            $multiDropAttributeCode => [
                                'attribute_code' => $multiDropAttributeCode,
                                'value' => "$multiDropAttributeValue1,$multiDropAttributeValue2",
                                'label' => "$multiDropAttributeLabel1, $multiDropAttributeLabel2"
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $attributeOptionDropNew1 = $this->getMockBuilder(
            \Magento\Eav\Api\Data\AttributeOptionInterface::class
        )
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $attributeOptionDropNew2 = $this->getMockBuilder(
            \Magento\Eav\Api\Data\AttributeOptionInterface::class
        )
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $attributeOptionMultidropDropNew1 = $this->getMockBuilder(
            \Magento\Eav\Api\Data\AttributeOptionInterface::class
        )
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $attributeOptionMultidropDropNew2 = $this->getMockBuilder(
            \Magento\Eav\Api\Data\AttributeOptionInterface::class
        )
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->serializerMock->expects($this->once())
            ->method('unserialize')
            ->with(
                json_encode($mockCheckoutConfig)
            )
            ->willReturn($mockCheckoutConfig);

        $this->serializerMock->expects($this->once())
            ->method('serialize')
            ->with(
                $expectedCheckoutConfig
            )
            ->willReturn(json_encode($expectedCheckoutConfig));

        $attributeOptionDropNew1->expects($this->once())
            ->method('getValue')
            ->will($this->returnValue($dropAttributeValue1));
        $attributeOptionDropNew1->expects($this->once())
            ->method('getLabel')
            ->will($this->returnValue($dropAttributeLabel1));

        $attributeOptionDropNew2->expects($this->once())
            ->method('getValue')
            ->will($this->returnValue($dropAttributeValue2));

        $attributeOptionMultidropDropNew1->expects($this->once())
            ->method('getValue')
            ->will($this->returnValue($multiDropAttributeValue1));
        $attributeOptionMultidropDropNew1->expects($this->once())
            ->method('getLabel')
            ->will($this->returnValue($multiDropAttributeLabel1));

        $attributeOptionMultidropDropNew2->expects($this->once())
            ->method('getValue')
            ->will($this->returnValue($multiDropAttributeValue2));

        $attributeOptionMultidropDropNew2->expects($this->once())
            ->method('getLabel')
            ->will($this->returnValue($multiDropAttributeLabel2));

        $this->attributeOptionManagerMock->expects($this->at(0))
            ->method('getItems')
            ->with(
                \Magento\Customer\Model\Indexer\Address\AttributeProvider::ENTITY,
                $dropAttributeCode
            )
            ->will($this->returnValue([$attributeOptionDropNew1, $attributeOptionDropNew2]));

        $this->attributeOptionManagerMock->expects($this->at(1))
            ->method('getItems')
            ->with(
                \Magento\Customer\Model\Indexer\Address\AttributeProvider::ENTITY,
                $textAttributeCode
            )
            ->will($this->returnValue(null));

        $this->attributeOptionManagerMock->expects($this->at(2))
            ->method('getItems')
            ->with(
                \Magento\Customer\Model\Indexer\Address\AttributeProvider::ENTITY,
                $multiDropAttributeCode
            )
            ->will($this->returnValue([$attributeOptionMultidropDropNew1, $attributeOptionMultidropDropNew2]));

        $this->resetCheckoutConfigOnOnePage = $this->objectManagerHelper->getObject(
            \Magento\Checkout\Plugin\Block\ResetCheckoutConfigOnOnePage::class,
            [
                'attributeOptionManager' => $this->attributeOptionManagerMock,
                'serializer' => $this->serializerMock
            ]
        );

        $result = $this->resetCheckoutConfigOnOnePage->afterGetSerializedCheckoutConfig(
            $this->onePageMock, json_encode($mockCheckoutConfig)
        );

        $this->assertEquals(
            $result,
            json_encode($expectedCheckoutConfig)
        );
    }

    /**
     *  Test for reformat serialized checkout config with options
     *  and other custom attributes in custom address for Onepage
     *
     * @covers \Magento\Checkout\Plugin\Block\ResetCheckoutConfigOnOnePage::afterGetSerializedCheckoutConfig()
     * @return void
     */
    public function testAfterGetSerializedCheckoutConfigWithOptionsAndOtherCustomAttributesInCustomAddressResults()
    {
        $dropAttributeCode = 'dropnew';
        $dropAttributeValue1 = 15;
        $dropAttributeLabel1 = 'drop 1';
        $dropAttributeValue2 = 16;
        $dropAttributeLabel2 = 'drop 2';
        $multiDropAttributeValue1 = 17;
        $multiDropAttributeLabel1 = 'multidrop 1';
        $multiDropAttributeValue2 = 18;
        $multiDropAttributeLabel2 = 'multidrop 2';
        $multiDropAttributeCode = 'multidrop';
        $mockCheckoutConfig = [
            'customerData' => [
                'addresses' => [
                    [
                        'custom_attributes' => [
                            $dropAttributeCode => [
                                'attribute_code' => $dropAttributeCode,
                                'value' => "$dropAttributeValue1",
                            ],
                            $multiDropAttributeCode => [
                                'attribute_code' => $multiDropAttributeCode,
                                'value' => "$multiDropAttributeValue1,$multiDropAttributeValue2",
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $expectedCheckoutConfig = [
            'customerData' => [
                'addresses' => [
                    [
                        'custom_attributes' => [
                            $dropAttributeCode => [
                                'attribute_code' => $dropAttributeCode,
                                'value' => $dropAttributeValue1,
                                'label' => $dropAttributeLabel1
                            ],
                            $multiDropAttributeCode => [
                                'attribute_code' => $multiDropAttributeCode,
                                'value' => "$multiDropAttributeValue1,$multiDropAttributeValue2",
                                'label' => "$multiDropAttributeLabel1, $multiDropAttributeLabel2"
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $attributeOptionDropNew1 = $this->getMockBuilder(
            \Magento\Eav\Api\Data\AttributeOptionInterface::class
        )
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $attributeOptionDropNew2 = $this->getMockBuilder(
            \Magento\Eav\Api\Data\AttributeOptionInterface::class
        )
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $attributeOptionMultidropDropNew1 = $this->getMockBuilder(
            \Magento\Eav\Api\Data\AttributeOptionInterface::class
        )
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $attributeOptionMultidropDropNew2 = $this->getMockBuilder(
            \Magento\Eav\Api\Data\AttributeOptionInterface::class
        )
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->serializerMock->expects($this->once())
            ->method('unserialize')
            ->with(
                json_encode($mockCheckoutConfig)
            )
            ->willReturn($mockCheckoutConfig);

        $this->serializerMock->expects($this->once())
            ->method('serialize')
            ->with(
                $expectedCheckoutConfig
            )
            ->willReturn(json_encode($expectedCheckoutConfig));

        $attributeOptionDropNew1->expects($this->once())
            ->method('getValue')
            ->will($this->returnValue($dropAttributeValue1));
        $attributeOptionDropNew1->expects($this->once())
            ->method('getLabel')
            ->will($this->returnValue($dropAttributeLabel1));

        $attributeOptionDropNew2->expects($this->once())
            ->method('getValue')
            ->will($this->returnValue($dropAttributeValue2));

        $attributeOptionMultidropDropNew1->expects($this->once())
            ->method('getValue')
            ->will($this->returnValue($multiDropAttributeValue1));
        $attributeOptionMultidropDropNew1->expects($this->once())
            ->method('getLabel')
            ->will($this->returnValue($multiDropAttributeLabel1));

        $attributeOptionMultidropDropNew2->expects($this->once())
            ->method('getValue')
            ->will($this->returnValue($multiDropAttributeValue2));
        $attributeOptionMultidropDropNew2->expects($this->once())
            ->method('getLabel')
            ->will($this->returnValue($multiDropAttributeLabel2));

        $this->attributeOptionManagerMock->expects($this->at(0))
            ->method('getItems')
            ->with(
                \Magento\Customer\Model\Indexer\Address\AttributeProvider::ENTITY,
                $dropAttributeCode
            )
            ->will($this->returnValue([$attributeOptionDropNew1, $attributeOptionDropNew2]));

        $this->attributeOptionManagerMock->expects($this->at(1))
            ->method('getItems')
            ->with(
                \Magento\Customer\Model\Indexer\Address\AttributeProvider::ENTITY,
                $multiDropAttributeCode
            )
            ->will($this->returnValue([$attributeOptionMultidropDropNew1, $attributeOptionMultidropDropNew2]));

        $this->resetCheckoutConfigOnOnePage = $this->objectManagerHelper->getObject(
            \Magento\Checkout\Plugin\Block\ResetCheckoutConfigOnOnePage::class,
            [
                'attributeOptionManager' => $this->attributeOptionManagerMock,
                'serializer' => $this->serializerMock
            ]
        );

        $result = $this->resetCheckoutConfigOnOnePage->afterGetSerializedCheckoutConfig(
            $this->onePageMock, json_encode($mockCheckoutConfig)
        );

        $this->assertEquals(
            $result,
            json_encode($expectedCheckoutConfig)
        );
    }
}

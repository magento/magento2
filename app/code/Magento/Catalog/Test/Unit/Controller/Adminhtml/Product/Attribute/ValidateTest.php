<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Controller\Adminhtml\Product\Attribute;

use Magento\Catalog\Controller\Adminhtml\Product\Attribute\Validate;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Catalog\Test\Unit\Controller\Adminhtml\Product\AttributeTest;
use Magento\Eav\Model\Entity\Attribute\Set as AttributeSet;
use Magento\Eav\Model\Validator\Attribute\Code as AttributeCodeValidator;
use Magento\Eav\Model\Validator\Attribute\Options;
use Magento\Framework\Controller\Result\Json as ResultJson;
use Magento\Framework\Controller\Result\JsonFactory as ResultJsonFactory;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Serialize\Serializer\FormData;
use Magento\Framework\View\LayoutFactory;
use Magento\Framework\View\LayoutInterface;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ValidateTest extends AttributeTest
{
    /**
     * @var ResultJsonFactory|MockObject
     */
    protected $resultJsonFactoryMock;

    /**
     * @var ResultJson|MockObject
     */
    protected $resultJson;

    /**
     * @var LayoutFactory|MockObject
     */
    protected $layoutFactoryMock;

    /**
     * @var ObjectManagerInterface|MockObject
     */
    protected $objectManagerMock;

    /**
     * @var Attribute|MockObject
     */
    protected $attributeMock;

    /**
     * @var AttributeSet|MockObject
     */
    protected $attributeSetMock;

    /**
     * @var Escaper|MockObject
     */
    protected $escaperMock;

    /**
     * @var LayoutInterface|MockObject
     */
    protected $layoutMock;

    /**
     * @var FormData|MockObject
     */
    private $formDataSerializerMock;

    /**
     * @var AttributeCodeValidator|MockObject
     */
    private $attributeCodeValidatorMock;

    /**
     * @var Options|MockObject
     */
    private $attributeOptionsValidatorMock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resultJsonFactoryMock = $this->getMockBuilder(ResultJsonFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultJson = $this->getMockBuilder(ResultJson::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->layoutFactoryMock = $this->getMockBuilder(LayoutFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->objectManagerMock = $this->getMockBuilder(ObjectManagerInterface::class)
            ->getMockForAbstractClass();
        $this->attributeMock = $this->getMockBuilder(Attribute::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->attributeSetMock = $this->getMockBuilder(AttributeSet::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->escaperMock = $this->getMockBuilder(Escaper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->layoutMock = $this->getMockBuilder(LayoutInterface::class)
            ->getMockForAbstractClass();
        $this->formDataSerializerMock = $this->getMockBuilder(FormData::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->attributeCodeValidatorMock = $this->getMockBuilder(AttributeCodeValidator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->attributeOptionsValidatorMock = $this->createMock(Options::class);
//        $this->attributeOptionsValidatorMock = $this->objectManager->getObject(Options::class);

        $this->contextMock->expects($this->any())
            ->method('getObjectManager')
            ->willReturn($this->objectManagerMock);
    }

    /**
     * {@inheritdoc}
     */
    protected function getModel()
    {
        return $this->objectManager->getObject(
            Validate::class,
            [
                'context' => $this->contextMock,
                'attributeLabelCache' => $this->attributeLabelCacheMock,
                'coreRegistry' => $this->coreRegistryMock,
                'resultPageFactory' => $this->resultPageFactoryMock,
                'resultJsonFactory' => $this->resultJsonFactoryMock,
                'layoutFactory' => $this->layoutFactoryMock,
                'multipleAttributeList' => ['select' => 'option'],
                'formDataSerializer' => $this->formDataSerializerMock,
                'attributeCodeValidator' => $this->attributeCodeValidatorMock,
                'attributeOptionsValidator' => $this->attributeOptionsValidatorMock
            ]
        );
    }

    public function testExecute()
    {
        $serializedOptions = '{"key":"value"}';
        $this->requestMock->expects($this->any())
            ->method('getParam')
            ->willReturnMap(
                [
                    ['frontend_label', null, 'test_frontend_label'],
                    ['attribute_code', null, 'test_attribute_code'],
                    ['new_attribute_set_name', null, 'test_attribute_set_name'],
                    ['serialized_options', '[]', $serializedOptions],
                ]
            );
        $this->objectManagerMock->expects($this->exactly(2))
            ->method('create')
            ->willReturnMap(
                [
                    [Attribute::class, [], $this->attributeMock],
                    [AttributeSet::class, [], $this->attributeSetMock]
                ]
            );
        $this->attributeMock->expects($this->once())
            ->method('loadByCode')
            ->willReturnSelf();

        $this->attributeCodeValidatorMock->expects($this->once())
            ->method('isValid')
            ->with('test_attribute_code')
            ->willReturn(true);

        $this->requestMock->expects($this->once())
            ->method('has')
            ->with('new_attribute_set_name')
            ->willReturn(true);
        $this->attributeSetMock->expects($this->once())
            ->method('setEntityTypeId')
            ->willReturnSelf();
        $this->attributeSetMock->expects($this->once())
            ->method('load')
            ->willReturnSelf();
        $this->attributeSetMock->expects($this->once())
            ->method('getId')
            ->willReturn(false);
        $this->resultJsonFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->resultJson);
        $this->resultJson->expects($this->once())
            ->method('setJsonData')
            ->willReturnSelf();

        $this->assertInstanceOf(ResultJson::class, $this->getModel()->execute());
    }

    /**
     * Test that editing existing attribute loads attribute by id
     *
     * @return void
     * @throws NotFoundException
     */
    public function testExecuteEditExisting(): void
    {
        $serializedOptions = '{"key":"value"}';
        $this->requestMock->expects($this->any())
            ->method('getParam')
            ->willReturnMap(
                [
                    ['frontend_label', null, 'test_frontend_label'],
                    ['attribute_id', null, 10],
                    ['attribute_code', null, 'test_attribute_code'],
                    ['new_attribute_set_name', null, 'test_attribute_set_name'],
                    ['serialized_options', '[]', $serializedOptions],
                ]
            );
        $this->objectManagerMock->expects($this->exactly(2))
            ->method('create')
            ->willReturnMap(
                [
                    [Attribute::class, [], $this->attributeMock],
                    [AttributeSet::class, [], $this->attributeSetMock]
                ]
            );
        $this->attributeMock->expects($this->once())
            ->method('load')
            ->willReturnSelf();
        $this->attributeMock->expects($this->once())
            ->method('getAttributeCode')
            ->willReturn('test_attribute_code');

        $this->attributeCodeValidatorMock->expects($this->once())
            ->method('isValid')
            ->with('test_attribute_code')
            ->willReturn(true);

        $this->requestMock->expects($this->once())
            ->method('has')
            ->with('new_attribute_set_name')
            ->willReturn(true);
        $this->attributeSetMock->expects($this->once())
            ->method('setEntityTypeId')
            ->willReturnSelf();
        $this->attributeSetMock->expects($this->once())
            ->method('load')
            ->willReturnSelf();
        $this->attributeSetMock->expects($this->once())
            ->method('getId')
            ->willReturn(false);
        $this->resultJsonFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->resultJson);
        $this->resultJson->expects($this->once())
            ->method('setJsonData')
            ->willReturnSelf();

        $this->assertInstanceOf(ResultJson::class, $this->getModel()->execute());
    }

    /**
     * @dataProvider provideUniqueData
     * @param array $options
     * @param boolean $isError
     * @throws       NotFoundException
     */
    public function testUniqueValidation(array $options, $isError)
    {
        $serializedOptions = '{"key":"value"}';
        $countFunctionCalls = ($isError) ? 7 : 6;
        $this->requestMock->expects($this->exactly($countFunctionCalls))
            ->method('getParam')
            ->willReturnMap(
                [
                    ['frontend_label', null, null],
                    ['attribute_code', null, "test_attribute_code"],
                    ['new_attribute_set_name', null, 'test_attribute_set_name'],
                    ['message_key', null, Validate::DEFAULT_MESSAGE_KEY],
                    ['serialized_options', '[]', $serializedOptions],
                ]
            );

        $this->formDataSerializerMock
            ->expects($this->once())
            ->method('unserialize')
            ->with($serializedOptions)
            ->willReturn($options);

        $this->attributeCodeValidatorMock->expects($this->once())
            ->method('isValid')
            ->with('test_attribute_code')
            ->willReturn(true);

        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->willReturn($this->attributeMock);

        $this->attributeMock->expects($this->once())
            ->method('loadByCode')
            ->willReturnSelf();

        $this->requestMock->expects($this->once())
            ->method('has')
            ->with('new_attribute_set_name')
            ->willReturn(false);

        $this->resultJsonFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->resultJson);

        $this->resultJson->expects($this->once())
            ->method('setJsonData')
            ->willReturnSelf();

        $this->assertInstanceOf(ResultJson::class, $this->getModel()->execute());
    }

    /**
     * @return array
     */
    public function provideUniqueData()
    {
        return [
            'no values' => [
                [
                    'option' => [
                        'delete' => [
                            "option_0" => "",
                            "option_1" => "",
                            "option_2" => "",
                        ],
                    ],
                ], false
            ],
            'valid options' => [
                [
                    'option' => [
                        'value' => [
                            "option_0" => ['1', '0'],
                            "option_1" => ['2', '0'],
                            "option_2" => ['3', '0'],
                        ],
                        'delete' => [
                            "option_0" => "",
                            "option_1" => "",
                            "option_2" => "",
                        ],
                    ],
                ], false
            ],
            'duplicate options' => [
                [
                    'option' => [
                        'value' => [
                            "option_0" => ['1', '0'],
                            "option_1" => ['1', '0'],
                            "option_2" => ['3', '0'],
                        ],
                        'delete' => [
                            "option_0" => "",
                            "option_1" => "",
                            "option_2" => "",
                        ],
                    ],
                ], true
            ],
            'duplicate and deleted' => [
                [
                    'option' => [
                        'value' => [
                            "option_0" => ['1', '0'],
                            "option_1" => ['1', '0'],
                            "option_2" => ['3', '0'],
                        ],
                        'delete' => [
                            "option_0" => "",
                            "option_1" => "1",
                            "option_2" => "",
                        ],
                    ],
                ], false
            ],
            'empty and deleted' => [
                [
                    'option' => [
                        'value' => [
                            "option_0" => ['1', '0'],
                            "option_1" => ['2', '0'],
                            "option_2" => ["", ""],
                        ],
                        'delete' => [
                            "option_0" => "",
                            "option_1" => "",
                            "option_2" => "1",
                        ],
                    ],
                ], false
            ],
        ];
    }

    /**
     * Check that empty admin scope labels will trigger error.
     *
     * @dataProvider provideEmptyOption
     * @param array $options
     * @throws       NotFoundException
     */
    public function testEmptyOption(array $options, $result)
    {
        $serializedOptions = '{"key":"value"}';
        $this->requestMock->expects($this->any())
            ->method('getParam')
            ->willReturnMap(
                [
                    ['frontend_label', null, null],
                    ['frontend_input', 'select', 'multipleselect'],
                    ['attribute_code', null, "test_attribute_code"],
                    ['new_attribute_set_name', null, 'test_attribute_set_name'],
                    ['message_key', Validate::DEFAULT_MESSAGE_KEY, 'message'],
                    ['serialized_options', '[]', $serializedOptions],
                ]
            );

        $this->formDataSerializerMock
            ->expects($this->once())
            ->method('unserialize')
            ->with($serializedOptions)
            ->willReturn($options);

        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->willReturn($this->attributeMock);

        $this->attributeMock->expects($this->once())
            ->method('loadByCode')
            ->willReturnSelf();

        $this->attributeCodeValidatorMock->expects($this->once())
            ->method('isValid')
            ->with('test_attribute_code')
            ->willReturn(true);

        $this->resultJsonFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->resultJson);

        $this->resultJson->expects($this->once())
            ->method('setJsonData')
            ->willReturnArgument(0);

        $this->attributeOptionsValidatorMock->expects($this->once())
            ->method('isValid')
            ->willReturn(!$result->error);

        if ($result->error) {
            $this->attributeOptionsValidatorMock->expects($this->once())
                ->method('getMessages')
                ->willReturn([$result->message]);
        }

        $response = $this->getModel()->execute();
        $responseObject = json_decode($response);
        $this->assertEquals($result, $responseObject);
    }

    /**
     * Dataprovider for testEmptyOption.
     *
     * @return array
     */
    public function provideEmptyOption()
    {
        return [
            'empty admin scope options' => [
                [
                    'option' => [
                        'value' => [
                            'option_0' => [''],
                        ],
                    ],
                ],
                (object)[
                    'error' => true,
                    'message' => 'The value of Admin scope can\'t be empty.',
                ]
            ],
            'not empty admin scope options' => [
                [
                    'option' => [
                        'value' => [
                            'option_0' => ['asdads'],
                        ],
                    ],
                ],
                (object)[
                    'error' => false,
                ]
            ],
            'empty admin scope options and deleted' => [
                [
                    'option' => [
                        'value' => [
                            'option_0' => [''],
                        ],
                        'delete' => [
                            'option_0' => '1',
                        ],
                    ],
                ],
                (object)[
                    'error' => false,
                ],
            ],
            'empty admin scope options and not deleted' => [
                [
                    'option' => [
                        'value' => [
                            'option_0' => [''],
                        ],
                        'delete' => [
                            'option_0' => '0',
                        ],
                    ],
                ],
                (object)[
                    'error' => true,
                    'message' => 'The value of Admin scope can\'t be empty.',
                ],
            ],
        ];
    }

    /**
     * Check that admin scope labels which only contain spaces will trigger error.
     *
     * @dataProvider provideWhitespaceOption
     * @param array $options
     * @param        $result
     * @throws       NotFoundException
     */
    public function testWhitespaceOption(array $options, $result)
    {
        $serializedOptions = '{"key":"value"}';
        $this->requestMock->expects($this->any())
            ->method('getParam')
            ->willReturnMap(
                [
                    ['frontend_label', null, null],
                    ['frontend_input', 'select', 'multipleselect'],
                    ['attribute_code', null, "test_attribute_code"],
                    ['new_attribute_set_name', null, 'test_attribute_set_name'],
                    ['message_key', Validate::DEFAULT_MESSAGE_KEY, 'message'],
                    ['serialized_options', '[]', $serializedOptions],
                ]
            );

        $this->formDataSerializerMock
            ->expects($this->once())
            ->method('unserialize')
            ->with($serializedOptions)
            ->willReturn($options);

        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->willReturn($this->attributeMock);

        $this->attributeMock->expects($this->once())
            ->method('loadByCode')
            ->willReturnSelf();

        $this->attributeCodeValidatorMock->expects($this->once())
            ->method('isValid')
            ->with('test_attribute_code')
            ->willReturn(true);

        $this->resultJsonFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->resultJson);

        $this->resultJson->expects($this->once())
            ->method('setJsonData')
            ->willReturnArgument(0);

        $this->attributeOptionsValidatorMock->expects($this->once())
            ->method('isValid')
            ->willReturn(!$result->error);

        if ($result->error) {
            $this->attributeOptionsValidatorMock->expects($this->once())
                ->method('getMessages')
                ->willReturn([$result->message]);
        }

        $response = $this->getModel()->execute();
        $responseObject = json_decode($response);
        $this->assertEquals($result, $responseObject);
    }

    /**
     * Dataprovider for testWhitespaceOption.
     *
     * @return array
     */
    public function provideWhitespaceOption()
    {
        return [
            'whitespace admin scope options' => [
                [
                    'option' => [
                        'value' => [
                            "option_0" => [' '],
                        ],
                    ],
                ],
                (object)[
                    'error' => true,
                    'message' => 'The value of Admin scope can\'t be empty.',
                ]
            ],
            'not empty admin scope options' => [
                [
                    'option' => [
                        'value' => [
                            "option_0" => ['asdads'],
                        ],
                    ],
                ],
                (object)[
                    'error' => false,
                ]
            ],
            'whitespace admin scope options and deleted' => [
                [
                    'option' => [
                        'value' => [
                            "option_0" => [' '],
                        ],
                        'delete' => [
                            'option_0' => '1',
                        ],
                    ],
                ],
                (object)[
                    'error' => false,
                ],
            ],
            'whitespace admin scope options and not deleted' => [
                [
                    'option' => [
                        'value' => [
                            "option_0" => [' '],
                        ],
                        'delete' => [
                            'option_0' => '0',
                        ],
                    ],
                ],
                (object)[
                    'error' => true,
                    'message' => 'The value of Admin scope can\'t be empty.',
                ],
            ],
        ];
    }

    /**
     * @throws NotFoundException
     */
    public function testExecuteWithOptionsDataError()
    {
        $serializedOptions = '{"key":"value"}';
        $message = "The attribute couldn't be validated due to an error. Verify your information and try again. "
            . "If the error persists, please try again later.";
        $this->requestMock->expects($this->any())
            ->method('getParam')
            ->willReturnMap(
                [
                    ['frontend_label', null, 'test_frontend_label'],
                    ['attribute_code', null, 'test_attribute_code'],
                    ['new_attribute_set_name', null, 'test_attribute_set_name'],
                    ['message_key', Validate::DEFAULT_MESSAGE_KEY, 'message'],
                    ['serialized_options', '[]', $serializedOptions],
                ]
            );

        $this->formDataSerializerMock
            ->expects($this->once())
            ->method('unserialize')
            ->with($serializedOptions)
            ->willThrowException(new \InvalidArgumentException('Some exception'));

        $this->objectManagerMock
            ->method('create')
            ->willReturnMap(
                [
                    [Attribute::class, [], $this->attributeMock],
                    [AttributeSet::class, [], $this->attributeSetMock]
                ]
            );

        $this->attributeCodeValidatorMock
            ->method('isValid')
            ->willReturn(true);

        $this->attributeMock
            ->method('loadByCode')
            ->willReturnSelf();
        $this->attributeSetMock
            ->method('setEntityTypeId')
            ->willReturnSelf();
        $this->resultJsonFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->resultJson);
        $this->resultJson->expects($this->once())
            ->method('setJsonData')
            ->with(
                json_encode(
                    [
                        'error' => true,
                        'message' => $message
                    ]
                )
            )
            ->willReturnSelf();

        $this->attributeOptionsValidatorMock->expects($this->once())
            ->method('getMessages')
            ->willReturn([$message]);

        $this->getModel()->execute();
    }

    /**
     * @throws NotFoundException
     */
    public function testExecuteWithHtmlOptionError()
    {
        $options = [
            'option' => [
                'value' => [
                    'option_0' => [
                        '<html>',
                    ],
                ],
            ],
        ];
        $serializedOptions = '{"key":"value"}';
        $message = 'HTML tags are not allowed for the attribute options. ' .
            'Those have been found in option "%1"';
        $this->requestMock->expects($this->any())
            ->method('getParam')
            ->willReturnMap(
                [
                    ['frontend_label', null, 'test_frontend_label'],
                    ['attribute_code', null, 'test_attribute_code'],
                    ['new_attribute_set_name', null, 'test_attribute_set_name'],
                    ['message_key', Validate::DEFAULT_MESSAGE_KEY, 'message'],
                    ['serialized_options', '[]', $serializedOptions],
                ]
            );

        $this->formDataSerializerMock
            ->expects($this->once())
            ->method('unserialize')
            ->with($serializedOptions)
            ->willReturn($options);

        $this->objectManagerMock
            ->method('create')
            ->willReturnMap(
                [
                    [Attribute::class, [], $this->attributeMock],
                    [AttributeSet::class, [], $this->attributeSetMock]
                ]
            );

        $this->attributeCodeValidatorMock
            ->method('isValid')
            ->willReturn(true);

        $this->attributeMock
            ->method('loadByCode')
            ->willReturnSelf();
        $this->attributeSetMock
            ->method('setEntityTypeId')
            ->willReturnSelf();
        $this->resultJsonFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->resultJson);
        $this->resultJson->expects($this->once())
            ->method('setJsonData')
            ->with(
                json_encode(
                    [
                        'error' => true,
                        'message' => $message
                    ]
                )
            )
            ->willReturnSelf();
        $this->attributeOptionsValidatorMock->expects($this->once())
            ->method('isValid')
            ->with($options['option']['value'])
            ->willReturn(false);

        $this->attributeOptionsValidatorMock->expects($this->once())
            ->method('getMessages')
            ->willReturn([$message]);

        $this->getModel()->execute();
    }

    /**
     * Test execute with an invalid attribute code
     *
     * @dataProvider provideInvalidAttributeCodes
     * @param string $attributeCode
     * @param        $result
     * @throws       NotFoundException
     */
    public function testExecuteWithInvalidAttributeCode($attributeCode, $result)
    {
        $serializedOptions = '{"key":"value"}';
        $this->requestMock->expects($this->any())
            ->method('getParam')
            ->willReturnMap(
                [
                    ['frontend_label', null, null],
                    ['frontend_input', 'select', 'multipleselect'],
                    ['attribute_code', null, $attributeCode],
                    ['new_attribute_set_name', null, 'test_attribute_set_name'],
                    ['message_key', Validate::DEFAULT_MESSAGE_KEY, 'message'],
                    ['serialized_options', '[]', $serializedOptions],
                ]
            );

        $this->formDataSerializerMock
            ->expects($this->once())
            ->method('unserialize')
            ->with($serializedOptions)
            ->willReturn(["key" => "value"]);

        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->willReturn($this->attributeMock);

        $this->attributeMock->expects($this->once())
            ->method('loadByCode')
            ->willReturnSelf();

        $this->attributeCodeValidatorMock->expects($this->once())
            ->method('isValid')
            ->with($attributeCode)
            ->willReturn(false);

        $this->attributeCodeValidatorMock->expects($this->once())
            ->method('getMessages')
            ->willReturn(['Invalid Attribute Code.']);

        $this->resultJsonFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->resultJson);

        $this->resultJson->expects($this->once())
            ->method('setJsonData')
            ->willReturnArgument(0);

        $this->attributeOptionsValidatorMock->expects($this->once())
            ->method('isValid')
            ->willReturn(!$result->error);

        if ($result->error) {
            $this->attributeOptionsValidatorMock->expects($this->once())
                ->method('getMessages')
                ->willReturn([$result->message]);
        }

        $response = $this->getModel()->execute();
        $responseObject = json_decode($response);

        $this->assertEquals($result, $responseObject);
    }

    /**
     * Providing invalid attribute codes
     *
     * @return array
     */
    public function provideInvalidAttributeCodes()
    {
        return [
            'invalid attribute code' => [
                '.attribute_code',
                (object)[
                    'error' => true,
                    'message' => 'Invalid Attribute Code.',
                ]
            ]
        ];
    }
}

<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Test\Unit\Consumer\Config\Validator;

use Magento\Framework\MessageQueue\Consumer\Config\Validator\Handlers as HandlersValidator;
use Magento\Framework\Reflection\MethodsMap;

/**
 * @codingStandardsIgnoreFile
 */
class HandlersTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MethodsMap|\PHPUnit_Framework_MockObject_MockObject
     */
    private $methodsMap;

    /**
     * @var HandlersValidator
     */
    private $validator;

    /**
     * Initialize parameters
     */
    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->methodsMap = $this->getMockBuilder(MethodsMap::class)->disableOriginalConstructor()->getMock();
        $this->validator = $objectManager->getObject(HandlersValidator::class, ['methodsMap' => $this->methodsMap]);
    }

    /**
     * @dataProvider validConfigDataProvider
     * @param array $configData
     */
    public function testValidateValid($configData)
    {
        $this->validator->validate($configData);
    }

    /**
     * @return array
     */
    public function validConfigDataProvider()
    {
        return [
            'valid' => [
                [
                    'consumer1' => [
                        'name' => 'consumer1',
                        'queue' => 'queue1',
                        'consumerInstance' => 'consumerClass1',
                        'handlers' => [
                            ['type' => 'handlerClassOne', 'method' => 'handlerMethodOne'],
                            ['type' => 'handlerClassTwo', 'method' => 'handlerMethodTwo'],
                        ],
                        'connection' => 'connection1',
                        'maxMessages' => '100',
                    ]
                ]
            ],
            'valid, empty handlers' => [
                [
                    'consumer1' => [
                        'name' => 'consumer1',
                        'queue' => 'queue1',
                        'consumerInstance' => 'consumerClass1',
                        'handlers' => [],
                        'connection' => 'connection1',
                        'maxMessages' => null,
                    ]
                ]
            ],
        ];
    }

    /**
     * @dataProvider invalidConfigDataProvider
     * @param array $configData
     * @param string $expectedExceptionMessage
     */
    public function testValidateInvalid($configData, $expectedExceptionMessage)
    {
        $this->setExpectedException('\LogicException', $expectedExceptionMessage);
        $this->validator->validate($configData);
    }

    /**
     * @return array
     */
    public function invalidConfigDataProvider()
    {
        return [
            'invalid, not an array' => [
                [
                    'consumer1' => [
                        'name' => 'consumer1',
                        'queue' => 'queue1',
                        'consumerInstance' => 'consumerClass1',
                        'handlers' => ['handlerClassOne::handlerMethodOne'],
                        'connection' => 'connection1',
                        'maxMessages' => '100',
                    ]
                ],
                "'consumer1' consumer declaration is invalid. Every handler element must be an array. It must contain 'type' and 'method' elements."
            ],
            'invalid, no required fields' => [
                [
                    'consumer1' => [
                        'name' => 'consumer1',
                        'queue' => 'queue1',
                        'consumerInstance' => 'consumerClass1',
                        'handlers' => [
                            ['handlerClassOne::handlerMethodOne']
                        ],
                        'connection' => 'connection1',
                        'maxMessages' => '100',
                    ]
                ],
                "'consumer1' consumer declaration is invalid. Every handler element must be an array. It must contain 'type' and 'method' elements."
            ],
            'invalid, no method' => [
                [
                    'consumer1' => [
                        'name' => 'consumer1',
                        'queue' => 'queue1',
                        'consumerInstance' => 'consumerClass1',
                        'handlers' => [
                            ['type' => 'handlerClassOne']
                        ],
                        'connection' => 'connection1',
                        'maxMessages' => '100',
                    ]
                ],
                "'consumer1' consumer declaration is invalid. Every handler element must be an array. It must contain 'type' and 'method' elements."
            ],
            'invalid, no type' => [
                [
                    'consumer1' => [
                        'name' => 'consumer1',
                        'queue' => 'queue1',
                        'consumerInstance' => 'consumerClass1',
                        'handlers' => [
                            ['method' => 'handlerMethodOne']
                        ],
                        'connection' => 'connection1',
                        'maxMessages' => '100',
                    ]
                ],
                "'consumer1' consumer declaration is invalid. Every handler element must be an array. It must contain 'type' and 'method' elements."
            ]
        ];
    }

    public function testValidateUndeclaredService()
    {
        $configData = [
            'consumer1' => [
                'name' => 'consumer1',
                'queue' => 'queue1',
                'consumerInstance' => 'consumerClass1',
                'handlers' => [
                    ['type' => 'handlerClassOne', 'method' => 'handlerMethodOne'],
                ],
                'connection' => 'connection1',
                'maxMessages' => '100',
            ]
        ];
        $expectedExceptionMessage = 'Service method specified as handler for of consumer "consumer1" is not available. Given "handlerClassOne::handlerMethodOne"';
        $this->setExpectedException('\LogicException', $expectedExceptionMessage);

        $this->methodsMap->expects($this->once())
            ->method('getMethodParams')
            ->with('handlerClassOne', 'handlerMethodOne')
            ->willThrowException(new \Exception(''));

        $this->validator->validate($configData);
    }
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Test\Unit\Model\Config\Export;

use Magento\Config\Model\Config\Export\Comment;
use Magento\Config\App\Config\Source\DumpConfigSourceInterface;
use Magento\Config\Model\Config\TypePool;
use Magento\Config\Model\Placeholder\PlaceholderFactory;
use Magento\Config\Model\Placeholder\PlaceholderInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class CommentTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var DumpConfigSourceInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $configSourceMock;

    /**
     * @var PlaceholderInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $placeholderMock;

    /**
     * @var TypePool|\PHPUnit\Framework\MockObject\MockObject
     */
    private $typePoolMock;

    /**
     * @var Comment
     */
    private $model;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->placeholderMock = $this->getMockBuilder(PlaceholderInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $placeholderFactoryMock = $this->getMockBuilder(PlaceholderFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $placeholderFactoryMock->expects($this->once())
            ->method('create')
            ->with(PlaceholderFactory::TYPE_ENVIRONMENT)
            ->willReturn($this->placeholderMock);

        $this->configSourceMock = $this->getMockBuilder(DumpConfigSourceInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->typePoolMock = $this->getMockBuilder(TypePool::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = $objectManager->getObject(
            Comment::class,
            [
                'placeholderFactory' => $placeholderFactoryMock,
                'source' => $this->configSourceMock,
                'typePool' => $this->typePoolMock,
            ]
        );
    }

    /**
     * @param array $sensitive
     * @param array $notSensitive
     * @param array $expectedMocks
     * @param $expectedMessage
     * @dataProvider dataProviderForTestGet
     */
    public function testGet(
        array $sensitive,
        array $notSensitive,
        array $expectedMocks,
        $expectedMessage
    ) {
        $this->configSourceMock->expects($this->once())
            ->method('getExcludedFields')
            ->willReturn(array_unique(array_merge($sensitive, $notSensitive)));
        $this->typePoolMock->expects($expectedMocks['typePoolMock']['isPresent']['expects'])
            ->method('isPresent')
            ->willReturnMap($expectedMocks['typePoolMock']['isPresent']['returnMap']);
        $this->placeholderMock->expects($expectedMocks['placeholderMock']['generate']['expects'])
            ->method('generate')
            ->willReturnMap($expectedMocks['placeholderMock']['generate']['returnMap']);

        $this->assertEquals($expectedMessage, $this->model->get());
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function dataProviderForTestGet()
    {
        return [
            [
                'sensitive' => [],
                'notSensitive' => [],
                'expectedMocks' => [
                    'typePoolMock' => [
                        'isPresent' => [
                            'expects' => $this->never(),
                            'returnMap' => [],
                        ]
                    ],
                    'placeholderMock' => [
                        'generate' => [
                            'expects' => $this->never(),
                            'returnMap' => [],
                        ],
                    ],
                ],
                'expectedMessage' => '',
            ],
            [
                'sensitive' => [],
                'notSensitive' => [
                    'some/notSensitive/field1',
                    'some/notSensitive/field2',
                ],
                'expectedMocks' => [
                    'typePoolMock' => [
                        'isPresent' => [
                            'expects' => $this->exactly(2),
                            'returnMap' => [
                                ['some/notSensitive/field1', TypePool::TYPE_SENSITIVE, false],
                                ['some/notSensitive/field2', TypePool::TYPE_SENSITIVE, false],
                            ]
                        ],
                    ],
                    'placeholderMock' => [
                        'generate' => [
                            'expects' => $this->never(),
                            'returnMap' => [],
                        ],
                    ],
                ],
                'expectedMessage' => ''
            ],
            [
                'sensitive' => ['some/sensitive/field1', 'some/sensitive/field2', 'some/sensitive_and_env/field'],
                'notSensitive' => ['some/notSensitive/field1', 'some/notSensitive/field2'],
                'expectedMocks' => [
                    'typePoolMock' => [
                        'isPresent' => [
                            'expects' => $this->exactly(5),
                            'returnMap' => [
                                ['some/sensitive/field1', TypePool::TYPE_SENSITIVE, true],
                                ['some/sensitive/field2', TypePool::TYPE_SENSITIVE, true],
                                ['some/sensitive_and_env/field', TypePool::TYPE_SENSITIVE, true],
                                ['some/notSensitive/field1', TypePool::TYPE_SENSITIVE, false],
                            ]
                        ],
                    ],
                    'placeholderMock' => [
                        'generate' => [
                            'expects' => $this->exactly(3),
                            'returnMap' => [
                                [
                                    'some/sensitive/field1',
                                    ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                                    null,
                                    'CONFIG__SOME__SENSITIVE__FIELD1'
                                ],
                                [
                                    'some/sensitive/field2',
                                    ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                                    null,
                                    'CONFIG__SOME__SENSITIVE__FIELD2'
                                ],
                                [
                                    'some/sensitive_and_env/field',
                                    ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                                    null,
                                    'CONFIG__SOME__SENSITIVE_AND_ENV__FIELD'
                                ],
                            ],
                        ],
                    ],
                ],
                'expectedMessage' => implode(PHP_EOL, [
                    'Shared configuration was written to config.php and system-specific configuration to env.php.',
                    'Shared configuration file (config.php) doesn\'t contain sensitive data for security reasons.',
                    'Sensitive data can be stored in the following environment variables:',
                    'CONFIG__SOME__SENSITIVE__FIELD1 for some/sensitive/field1',
                    'CONFIG__SOME__SENSITIVE__FIELD2 for some/sensitive/field2',
                    'CONFIG__SOME__SENSITIVE_AND_ENV__FIELD for some/sensitive_and_env/field'
                ])
            ],
        ];
    }
}

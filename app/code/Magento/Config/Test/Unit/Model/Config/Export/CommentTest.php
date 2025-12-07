<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Config\Test\Unit\Model\Config\Export;

use Magento\Config\App\Config\Source\DumpConfigSourceInterface;
use Magento\Config\Model\Config\Export\Comment;
use Magento\Config\Model\Config\TypePool;
use Magento\Config\Model\Placeholder\PlaceholderFactory;
use Magento\Config\Model\Placeholder\PlaceholderInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class CommentTest extends TestCase
{
    /**
     * @var DumpConfigSourceInterface|MockObject
     */
    private $configSourceMock;

    /**
     * @var PlaceholderInterface|MockObject
     */
    private $placeholderMock;

    /**
     * @var TypePool|MockObject
     */
    private $typePoolMock;

    /**
     * @var Comment
     */
    private $model;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->placeholderMock = $this->createMock(PlaceholderInterface::class);

        $placeholderFactoryMock = $this->createPartialMock(PlaceholderFactory::class, ['create']);

        $placeholderFactoryMock->expects($this->once())
            ->method('create')
            ->with(PlaceholderFactory::TYPE_ENVIRONMENT)
            ->willReturn($this->placeholderMock);

        $this->configSourceMock = $this->createMock(DumpConfigSourceInterface::class);

        $this->typePoolMock = $this->createMock(TypePool::class);

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
     */
    #[DataProvider('dataProviderForTestGet')]
    public function testGet(
        array $sensitive,
        array $notSensitive,
        array $expectedMocks,
        $expectedMessage
    ) {
        $this->configSourceMock->expects($this->once())
            ->method('getExcludedFields')
            ->willReturn(array_unique(array_merge($sensitive, $notSensitive)));
        
        // Convert string/array expects to actual matcher for typePoolMock
        $typePoolExpects = $expectedMocks['typePoolMock']['isPresent']['expects'];
        if (is_string($typePoolExpects)) {
            $typePoolMatcher = $this->{$typePoolExpects}();
        } elseif (is_array($typePoolExpects)) {
            $typePoolMatcher = $this->{$typePoolExpects[0]}($typePoolExpects[1]);
        } else {
            $typePoolMatcher = $typePoolExpects;
        }
        $this->typePoolMock->expects($typePoolMatcher)
            ->method('isPresent')
            ->willReturnMap($expectedMocks['typePoolMock']['isPresent']['returnMap']);
        
        // Convert string/array expects to actual matcher for placeholderMock
        $placeholderExpects = $expectedMocks['placeholderMock']['generate']['expects'];
        if (is_string($placeholderExpects)) {
            $placeholderMatcher = $this->{$placeholderExpects}();
        } elseif (is_array($placeholderExpects)) {
            $placeholderMatcher = $this->{$placeholderExpects[0]}($placeholderExpects[1]);
        } else {
            $placeholderMatcher = $placeholderExpects;
        }
        $this->placeholderMock->expects($placeholderMatcher)
            ->method('generate')
            ->willReturnMap($expectedMocks['placeholderMock']['generate']['returnMap']);

        $this->assertEquals($expectedMessage, $this->model->get());
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public static function dataProviderForTestGet()
    {
        return [
            [
                'sensitive' => [],
                'notSensitive' => [],
                'expectedMocks' => [
                    'typePoolMock' => [
                        'isPresent' => [
                            'expects' => 'never',
                            'returnMap' => [],
                        ]
                    ],
                    'placeholderMock' => [
                        'generate' => [
                            'expects' => 'never',
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
                            'expects' => ['exactly', 2],
                            'returnMap' => [
                                ['some/notSensitive/field1', TypePool::TYPE_SENSITIVE, false],
                                ['some/notSensitive/field2', TypePool::TYPE_SENSITIVE, false],
                            ]
                        ],
                    ],
                    'placeholderMock' => [
                        'generate' => [
                            'expects' => 'never',
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
                            'expects' => ['exactly', 5],
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
                            'expects' => ['exactly', 3],
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

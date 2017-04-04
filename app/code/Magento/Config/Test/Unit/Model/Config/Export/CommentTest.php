<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Test\Unit\Model\Config\Export;

use Magento\Config\App\Config\Source\DumpConfigSourceInterface;
use Magento\Config\Model\Config\Export\Comment;
use Magento\Config\Model\Config\Export\ExcludeList;
use Magento\Config\Model\Config\TypePool;
use Magento\Config\Model\Placeholder\PlaceholderFactory;
use Magento\Config\Model\Placeholder\PlaceholderInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class CommentTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DumpConfigSourceInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configSourceMock;

    /**
     * @var PlaceholderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $placeholderMock;

    /**
     * @var TypePool|\PHPUnit_Framework_MockObject_MockObject
     */
    private $typePoolMock;

    /**
     * @var ExcludeList|\PHPUnit_Framework_MockObject_MockObject
     */
    private $excludeListMock;

    /**
     * @var Comment
     */
    private $model;

    protected function setUp()
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

        $this->excludeListMock = $this->getMockBuilder(ExcludeList::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = $objectManager->getObject(
            Comment::class,
            [
                'placeholderFactory' => $placeholderFactoryMock,
                'source' => $this->configSourceMock,
                'typePool' => $this->typePoolMock,
                'excludeList' => $this->excludeListMock
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
        $this->excludeListMock->expects($expectedMocks['excludeListMock']['isPresent']['expects'])
            ->method('isPresent')
            ->willReturnMap($expectedMocks['excludeListMock']['isPresent']['returnMap']);
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
                    'excludeListMock' => [
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
                    'excludeListMock' => [
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
                'sensitive' => ['some/sensitive/field1', 'some/sensitive/field2'],
                'notSensitive' => ['some/notSensitive/field1', 'some/notSensitive/field2'],
                'expectedMocks' => [
                    'typePoolMock' => [
                        'isPresent' => [
                            'expects' => $this->exactly(4),
                            'returnMap' => [
                                ['some/sensitive/field1', TypePool::TYPE_SENSITIVE, true],
                                ['some/sensitive/field2', TypePool::TYPE_SENSITIVE, false],
                                ['some/notSensitive/field1', TypePool::TYPE_SENSITIVE, false],
                                ['some/notSensitive/field2', TypePool::TYPE_SENSITIVE, false],
                            ]
                        ],
                    ],
                    'excludeListMock' => [
                        'isPresent' => [
                            'expects' => $this->exactly(3),
                            'returnMap' => [
                                ['some/sensitive/field2', true],
                                ['some/notSensitive/field1', false],
                                ['some/notSensitive/field2', false],
                            ]
                        ],
                    ],
                    'placeholderMock' => [
                        'generate' => [
                            'expects' => $this->exactly(2),
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
                            ],
                        ],
                    ],
                ],
                'expectedMessage' => 'The configuration file doesn\'t contain sensitive data for security reasons. '
                    . 'Sensitive data can be stored in the following environment variables:'
                    . "\n" . 'CONFIG__SOME__SENSITIVE__FIELD1 for some/sensitive/field1'
                    . "\n" . 'CONFIG__SOME__SENSITIVE__FIELD2 for some/sensitive/field2'
            ],
        ];
    }
}

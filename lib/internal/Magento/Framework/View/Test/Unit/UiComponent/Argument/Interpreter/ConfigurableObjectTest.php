<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\View\Test\Unit\UiComponent\Argument\Interpreter;

use Magento\Framework\Code\Reader\ClassReader;
use Magento\Framework\Data\Argument\InterpreterInterface;
use Magento\Framework\ObjectManager\ConfigInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\UiComponent\Argument\Interpreter\ConfigurableObject;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Unit tests for ConfigurableObject
 */
class ConfigurableObjectTest extends TestCase
{
    /**
     * @var ConfigurableObject
     */
    private $configurableObject;

    /**
     * @var ObjectManagerInterface|MockObject
     */
    private $objectManager;

    /**
     * @var InterpreterInterface|MockObject
     */
    private $interpreter;

    /**
     * @var ClassReader|MockObject
     */
    private $classReader;

    /**
     * @var ConfigInterface|MockObject
     */
    private $objectManagerConfig;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->objectManager = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        $this->interpreter = $this->getMockForAbstractClass(InterpreterInterface::class);
        $this->classReader = $this->createMock(ClassReader::class);
        $this->objectManagerConfig = $this->getMockForAbstractClass(ConfigInterface::class);
        $this->configurableObject = $objectManager->getObject(
            ConfigurableObject::class,
            [
                'objectManager' => $this->objectManager,
                'argumentInterpreter' => $this->interpreter,
                'classWhitelist' => [
                    // @phpstan-ignore-next-line
                    \Foo\Bar\ClassA::class,
                    // @phpstan-ignore-next-line
                    \Foo\Bar\InterfaceA::class,
                ],
                'classReader' => $this->classReader,
                'objectManagerConfig' => $this->objectManagerConfig,
                'deniedClassList' => [
                    // @phpstan-ignore-next-line
                    \Foo\Bar\ClassC::class,
                    // @phpstan-ignore-next-line
                    \Foo\Bar\InterfaceC::class,
                ],
            ]
        );
    }

    /**
     * @dataProvider validDataProvider
     */
    public function testValidCombinations(
        $data,
        $expectedClass,
        $classParentsValueMap,
        $expectedArguments
    ) {
        $this->objectManagerConfig
            ->method('getPreference')
            ->with($expectedClass)
            ->WillReturn('bar');
        $this->objectManagerConfig
            ->method('getInstanceType')
            ->with('bar')
            ->willReturn($expectedClass);

        $this->classReader
            ->method('getParents')
            ->willReturnMap($classParentsValueMap);

        $this->objectManager
            ->expects($this->once())
            ->method('create')
            ->with($expectedClass, $expectedArguments)
            ->willReturn('an object yay!');

        $this->interpreter
            ->method('evaluate')
            ->willReturnCallback(
                function (array $arg) {
                    return $arg['value'];
                }
            );

        $actualResult = $this->configurableObject->evaluate($data);
        self::assertSame('an object yay!', $actualResult);
    }

    /**
     * @dataProvider invalidDataProvider
     */
    public function testInvalidCombinations(
        $data,
        $expectedClass,
        $classParentsValueMap,
        $expectedException,
        $expectedExceptionMessage
    ) {

        $this->expectException($expectedException);
        $this->expectExceptionMessage($expectedExceptionMessage);

        $this->objectManagerConfig
            ->method('getPreference')
            ->with($expectedClass)
            ->WillReturn('bar');
        $this->objectManagerConfig
            ->method('getInstanceType')
            ->with('bar')
            ->willReturn($expectedClass);

        $this->classReader
            ->method('getParents')
            ->willReturnMap($classParentsValueMap);

        $this->objectManager
            ->expects($this->never())
            ->method('create');

        $this->interpreter
            ->method('evaluate')
            ->willReturnCallback(
                function (array $arg) {
                    return $arg['value'];
                }
            );

        $actualResult = $this->configurableObject->evaluate($data);
        self::assertSame('an object yay!', $actualResult);
    }

    public function validDataProvider()
    {
        return [
            // Test most basic syntax with no arguments
            [
                [
                    'value' => 'MyObject',
                ],
                'MyObject',
                [],
                []
            ],
            // Test alternative data syntax
            [
                [
                    'argument' => [
                        'class' => ['value' => 'MyFooClass']
                    ]
                ],
                'MyFooClass',
                [
                    ['MyFooClass', ['Something', 'skipme']],
                    ['Something', ['dontcare', 'SomethingElse']],
                    // @phpstan-ignore-next-line
                    ['SomethingElse', [\Foo\Bar\ClassA::class, 'unrelated']],
                    ['skipme', []],
                    ['dontcare', []],
                    ['unrelated', []],
                    // @phpstan-ignore-next-line
                    [\Foo\Bar\ClassA::class, []]
                ],
                []
            ],
            // Test arguments
            [
                [
                    'argument' => [
                        'class' => ['value' => 'MyFooClass'],
                        'myarg' => ['value' => 'bar'],
                    ]
                ],
                'MyFooClass',
                [
                    ['MyFooClass', ['Something', 'skipme']],
                    ['Something', ['dontcare', 'SomethingElse']],
                    // @phpstan-ignore-next-line
                    ['SomethingElse', [\Foo\Bar\ClassA::class, 'unrelated']],
                    ['skipme', []],
                    ['dontcare', []],
                    ['unrelated', []],
                    // @phpstan-ignore-next-line
                    [\Foo\Bar\ClassA::class, []]
                ],
                ['myarg' => 'bar']
            ],
            // Test multiple matching whitelisted classes
            [
                [
                    'argument' => [
                        'class' => ['value' => 'MyFooClass'],
                        'myarg' => ['value' => 'bar'],
                    ]
                ],
                'MyFooClass',
                [
                    ['MyFooClass', ['Something', 'skipme']],
                    ['Something', ['dontcare', 'SomethingElse']],
                    // @phpstan-ignore-next-line
                    ['SomethingElse', [\Foo\Bar\ClassA::class, 'unrelated']],
                    ['skipme', []],
                    ['dontcare', []],
                    // @phpstan-ignore-next-line
                    ['unrelated', [\Foo\Bar\InterfaceA::class]],
                    // @phpstan-ignore-next-line
                    [\Foo\Bar\ClassA::class, []],
                    // @phpstan-ignore-next-line
                    [\Foo\Bar\InterfaceA::class, []]
                ],
                ['myarg' => 'bar']
            ],
        ];
    }

    public function invalidDataProvider()
    {
        return [
            [
                [
                    'notvalid' => 'sup'
                ],
                '',
                [],
                \InvalidArgumentException::class,
                'Node "argument" required for this type.'
            ],
            [
                [
                    'argument' => [
                        'notclass' => ['value' => 'doesntmatter']
                    ]
                ],
                '',
                [],
                \InvalidArgumentException::class,
                'Node "argument" with name "class" is required for this type.'
            ],
            [
                [
                    'argument' => [
                        'class' => ['value' => 'MyFooClass'],
                        'myarg' => ['value' => 'bar'],
                    ]
                ],
                'MyFooClass',
                [
                    ['MyFooClass', ['Something', 'skipme']],
                    ['Something', ['dontcare', 'SomethingElse']],
                    ['SomethingElse', ['unrelated']],
                    ['skipme', []],
                    ['dontcare', []],
                    ['unrelated', []],
                ],
                \InvalidArgumentException::class,
                'Class argument is invalid: MyFooClass'
            ],
            [
                [
                    'argument' => [
                        'class' => ['value' => 'MyFooClass'],
                        'myarg' => ['value' => 'bar'],
                    ],
                ],
                'MyFooClass',
                [
                    ['MyFooClass', ['Something', 'skipme']],
                    ['Something', ['dontcare', 'SomethingElse']],
                    // @phpstan-ignore-next-line
                    ['SomethingElse', [\Foo\Bar\ClassC::class, 'unrelated']],
                    ['skipme', []],
                    ['dontcare', []],
                    // @phpstan-ignore-next-line
                    ['unrelated', [\Foo\Bar\InterfaceC::class]],
                    // @phpstan-ignore-next-line
                    [\Foo\Bar\ClassC::class, []],
                    // @phpstan-ignore-next-line
                    [\Foo\Bar\InterfaceC::class, []],
                ],
                \InvalidArgumentException::class,
                'Class argument is invalid: MyFooClass',
            ],
        ];
    }
}

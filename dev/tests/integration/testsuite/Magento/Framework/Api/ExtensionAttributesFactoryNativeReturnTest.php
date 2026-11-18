<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Api;

use PHPUnit\Framework\Attributes\DataProvider;

class ExtensionAttributesFactoryNativeReturnTest extends \PHPUnit\Framework\TestCase
{
    #[DataProvider('getterReturnTypeProvider')]
    public function testGetterReturnType(string $fixtureName, bool $accepted): void
    {
        require_once __DIR__ . '/_files/ExtensionAttributesFactory/' . $fixtureName . 'Interface.php';
        $interfaceName = 'Magento\\Framework\\Api\\TestFixture\\' . $fixtureName . 'Interface';
        $objectManager = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);
        $factory = new ExtensionAttributesFactory($objectManager);

        if (!$accepted) {
            $objectManager->expects(self::never())->method('create');
            $this->expectException(\LogicException::class);
            $this->expectExceptionMessage(
                "Method 'getExtensionAttributes' must be overridden in the interfaces "
                . "which extend 'Magento\\Framework\\Api\\ExtensibleDataInterface'. "
                . "Concrete return type must be specified. Please fix :\\" . $interfaceName
            );
            $factory->create($interfaceName);
            return;
        }

        $data = ['attribute' => 'value'];
        $extension = $this->createStub(ExtensionAttributesInterface::class);
        $extensionFactory = $this->createMock(ExtensionAttributesFactory::class);
        $extensionFactory->expects(self::once())->method('create')->with($data)->willReturn($extension);
        $objectManager->expects(self::once())->method('create')
            ->with('\\Magento\\Framework\\Api\\TestFixture\\' . $fixtureName . 'ExtensionFactory')
            ->willReturn($extensionFactory);

        self::assertSame($extension, $factory->create($interfaceName, $data));
    }

    public static function getterReturnTypeProvider(): array
    {
        return [
            'native only' => ['NativeOnly', true],
            'returns in description' => ['NativeReturnsDescription', true],
            'nullable native with description' => ['NativeDescription', true],
            'wrong documented return takes precedence' => ['WrongDocReturn', false],
            'empty documented return takes precedence' => ['EmptyDocReturn', false],
            'no return type' => ['MissingReturn', false],
            'documented return' => ['DocumentedReturn', true],
            'wrong native return' => ['WrongNativeReturn', false],
            'union native return' => ['UnionNativeReturn', false],
        ];
    }
}

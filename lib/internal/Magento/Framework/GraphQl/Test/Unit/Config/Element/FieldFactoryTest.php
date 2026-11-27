<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Test\Unit\Config\Element;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Config\Element\FieldFactory;
use Magento\Framework\ObjectManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Test the transmission of custom directives from the config data to the Field element
 */
class FieldFactoryTest extends TestCase
{
    /**
     * @param array $fieldData
     * @param array $expectedDirectives
     */
    #[DataProvider('directivesDataProvider')]
    public function testCreateFromConfigDataPassesDirectives(array $fieldData, array $expectedDirectives): void
    {
        $objectManager = $this->createMock(ObjectManagerInterface::class);
        $objectManager->expects($this->once())
            ->method('create')
            ->with(Field::class, $this->callback(
                static fn (array $arguments): bool => $arguments['directives'] === $expectedDirectives
            ))
            ->willReturnCallback(static fn (string $class, array $arguments): Field => new Field(...$arguments));

        $field = (new FieldFactory($objectManager))->createFromConfigData($fieldData);

        $this->assertSame($expectedDirectives, $field->getDirectives());
    }

    /**
     * @return array
     */
    public static function directivesDataProvider(): array
    {
        $directives = ['my_directive' => [['name' => 'first']]];

        return [
            'no directive' => [['name' => 'foo', 'type' => 'String'], []],
            'custom directive' => [['name' => 'foo', 'type' => 'String', 'directives' => $directives], $directives],
        ];
    }
}

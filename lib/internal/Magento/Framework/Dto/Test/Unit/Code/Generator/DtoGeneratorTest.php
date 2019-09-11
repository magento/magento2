<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Dto\Test\Unit\Code\Generator;

use Magento\Framework\Code\Generator\ClassGenerator;
use Magento\Framework\Code\Generator\ClassGeneratorFactory;
use Magento\Framework\Code\Generator\InterfaceGenerator;
use Magento\Framework\Code\Generator\InterfaceGeneratorFactory;
use Magento\Framework\Dto\Code\GetDtoSourceCode;
use Magento\Framework\Dto\DtoConfig;
use Magento\Framework\Reflection\TypeProcessor;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DtoGeneratorTest extends TestCase
{
    /**
     * @var GetDtoSourceCode
     */
    private $getDtoSourceCode;

    /**
     * @var DtoConfig|MockObject
     */
    private $dtoConfig;

    /**
     * Prepare test env
     */
    protected function setUp()
    {
        $this->dtoConfig = $this->createMock(DtoConfig::class);

        $om = new ObjectManager($this);

        $this->getDtoSourceCode = $om->getObject(
            GetDtoSourceCode::class,
            [
                'dtoConfig' => $this->dtoConfig,
                'typeProcessor' => $om->getObject(TypeProcessor::class)
            ]
        );
    }

    /**
     * @return array
     */
    public function shouldGenerateDtoDataProvider(): array
    {
        $properties = [
            'prop1' => [
                'type' => 'string',
                'nullable' => false,
                'optional' => false
            ],
            'prop2' => [
                'type' => 'string',
                'nullable' => false,
                'optional' => true
            ],
            'prop3' => [
                'type' => 'string',
                'nullable' => true,
                'optional' => false
            ],
            'prop4' => [
                'type' => 'string',
                'nullable' => true,
                'optional' => true
            ]
        ];

        return [
            'SimpleImmutableDto' => [
                'dtoClass' => 'Magento\Framework\Dto\Test\Unit\Code\Generator\TestDto',
                'file' => 'SimpleImmutableDto.txt',
                'config' => [
                    'type' => 'class',
                    'interface' => 'Magento\Framework\Dto\Test\Unit\Code\Generator\TestDtoInterface',
                    'mutable' => false,
                    'properties' => $properties
                ]
            ],
            'SimpleDto' => [
                'dtoClass' => 'Magento\Framework\Dto\Test\Unit\Code\Generator\TestDto',
                'file' => 'SimpleDto.txt',
                'config' => [
                    'type' => 'class',
                    'interface' => 'Magento\Framework\Dto\Test\Unit\Code\Generator\TestDtoInterface',
                    'mutable' => true,
                    'properties' => $properties
                ]
            ],
            'SimpleImmutableDtoInterface' => [
                'dtoClass' => 'Magento\Framework\Dto\Test\Unit\Code\Generator\TestDtoInterface',
                'file' => 'SimpleImmutableDtoInterface.txt',
                'config' => [
                    'type' => 'interface',
                    'class' => 'Magento\Framework\Dto\Test\Unit\Code\Generator\TestDto',
                    'mutable' => false,
                    'properties' => $properties
                ]
            ],
            'SimpleDtoInterface' => [
                'dtoClass' => 'Magento\Framework\Dto\Test\Unit\Code\Generator\TestDtoInterface',
                'file' => 'SimpleDtoInterface.txt',
                'config' => [
                    'type' => 'interface',
                    'class' => 'Magento\Framework\Dto\Test\Unit\Code\Generator\TestDto',
                    'mutable' => true,
                    'properties' => $properties
                ]
            ],
            'ComplexDto' => [
                'dtoClass' => 'Magento\Framework\Dto\Test\Unit\Code\Generator\TestDto',
                'file' => 'ComplexDto.txt',
                'config' => [
                    'type' => 'class',
                    'interface' => 'Magento\Framework\Dto\Test\Unit\Code\Generator\TestDtoInterface',
                    'mutable' => false,
                    'properties' => [
                        'prop1' => [
                            'type' => 'array',
                            'nullable' => false,
                            'optional' => false
                        ],
                        'prop2' => [
                            'type' => 'Magento\Framework\Dto\Test\Unit\Code\Generator\TestObject',
                            'nullable' => false,
                            'optional' => false
                        ],
                        'prop3' => [
                            'type' => 'Magento\Framework\Dto\Test\Unit\Code\Generator\TestObject[]',
                            'nullable' => false,
                            'optional' => false
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * @dataProvider shouldGenerateDtoDataProvider
     * @param string $dtoClass
     * @param string $file
     * @param array $config
     * @return void
     */
    public function testShouldGenerateDto(string $dtoClass, string $file, array $config): void
    {
        $this->dtoConfig
            ->method('isDto')
            ->with($dtoClass)
            ->willReturn(true);

        $this->dtoConfig
            ->method('get')
            ->with($dtoClass)
            ->willReturn($config);

        $generatedDto = $this->getDtoSourceCode->execute($dtoClass);
        $this->assertStringEqualsFile(__DIR__ . '/_files/' . $file, $generatedDto);
    }
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Config\Test\Unit\App\Config\Source;

use Magento\Config\App\Config\Source\ConfigStructureSource;
use Magento\Config\Model\Config\Structure;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ConfigStructureSourceTest extends TestCase
{
    /**
     * @var Structure|MockObject
     */
    private $structure;

    /**
     * @var ConfigStructureSource
     */
    private $source;

    protected function setUp(): void
    {
        $this->structure = $this->createMock(Structure::class);
        $this->source = new ConfigStructureSource($this->structure);
    }

    /**
     * @dataProvider getDataProvider
     * @param array $fieldPaths
     * @param array $expectedConfig
     */
    public function testGet(array $fieldPaths, array $expectedConfig)
    {
        $this->structure->expects($this->once())
            ->method('getFieldPaths')
            ->willReturn($fieldPaths);
        $this->assertEquals($expectedConfig, $this->source->get('default'));
    }

    /**
     * @return array
     */
    public function getDataProvider(): array
    {
        return [
            [
                [
                    'general/single_store_mode/enabled' => [],
                    'general/locale/timezone' => [],
                    'general/locale/code' => [],
                ],
                [
                    'general' => [
                        'single_store_mode' => [
                            'enabled' => null,
                        ],
                        'locale' => [
                            'timezone' => null,
                            'code' => null,
                        ],
                    ],
                ],
            ],
        ];
    }
}

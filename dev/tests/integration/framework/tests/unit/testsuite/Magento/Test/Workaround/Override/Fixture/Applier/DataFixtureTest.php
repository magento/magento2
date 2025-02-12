<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Test\Workaround\Override\Fixture\Applier;

use Magento\TestFramework\Workaround\Override\Fixture\Applier\DataFixture;
use PHPUnit\Framework\TestCase;

/**
 * Provide tests for \Magento\TestFramework\Workaround\Override\Fixture\Applier\DataFixture
 */
class DataFixtureTest extends TestCase
{
    /** @var DataFixture */
    private $object;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->object = new DataFixture();
    }

    /**
     * @return void
     */
    public function testGetPrioritizedConfig(): void
    {
        $this->object = $this->getMockBuilder(DataFixture::class)
            ->onlyMethods(['getGlobalConfig','getClassConfig', 'getMethodConfig', 'getDataSetConfig'])
            ->getMock();
        $this->object->expects($this->once())
            ->method('getGlobalConfig')
            ->willReturn(['global_config']);
        $this->object->expects($this->once())
            ->method('getClassConfig')
            ->willReturn(['class_config']);
        $this->object->expects($this->once())
            ->method('getMethodConfig')
            ->willReturn(['method_config']);
        $this->object->expects($this->once())
            ->method('getDataSetConfig')
            ->willReturn(['data_set_config']);
        $expectedResult = [
            ['global_config'],
            ['class_config'],
            ['method_config'],
            ['data_set_config'],
        ];
        $reflectionMethod = new \ReflectionMethod(DataFixture::class, 'getPrioritizedConfig');
        $reflectionMethod->setAccessible(true);
        $this->assertEquals($expectedResult, $reflectionMethod->invoke($this->object));
    }

    /**
     * @dataProvider fixturesProvider
     *
     * @param array $existingFixtures
     * @param array $config
     * @param array $expectedOrder
     * @return void
     */
    public function testSortFixtures(array $existingFixtures, array $config, array $expectedOrder): void
    {
        $fixtures = $this->processApply($existingFixtures, $config);
        $this->assertEquals($expectedOrder, $fixtures);
    }

    /**
     * @return array
     */
    public static function fixturesProvider(): array
    {
        return [
            'sort_fixtures_before_all' => [
                'existingFixtures' => [['factory' => 'fixture']],
                'config' => [
                    [
                        'path' => 'added_fixture',
                        'newPath' => null,
                        'before' => '-',
                        'after' => null,
                        'remove' => false,
                    ]
                ],
                'expectedOrder' => [['factory' => 'added_fixture'], ['factory' => 'fixture']],
            ],
            'sort_fixtures_after_all' => [
                'existingFixtures' => [['factory' => 'fixture']],
                'config' => [
                    [
                        'path' => 'added_fixture',
                        'newPath' => null,
                        'before' => null,
                        'after' => '-',
                        'remove' => false,
                    ]
                ],
                'expectedOrder' => [['factory' => 'fixture'], ['factory' => 'added_fixture']],
            ],
            'sort_fixture_before_specific' => [
                'existingFixtures' => [['factory' => 'fixture1'], ['factory' => 'fixture2']],
                'config' => [
                    [
                        'path' => 'added_fixture',
                        'newPath' => null,
                        'before' => 'fixture2',
                        'after' => null,
                        'remove' => false,
                    ]
                ],
                'expectedOrder' => [
                    ['factory' => 'fixture1'],
                    ['factory' => 'added_fixture'],
                    ['factory' => 'fixture2']
                ],
            ],
            'sort_fixture_after_specific' => [
                'existingFixtures' => [
                    ['factory' => 'fixture1'],
                    ['factory' => 'fixture2'],
                    ['factory' => 'fixture3']
                ],
                'config' => [
                    [
                        'path' => 'added_fixture',
                        'newPath' => null,
                        'before' => null,
                        'after' => 'fixture2',
                        'remove' => false,
                    ]
                ],
                'expectedOrder' => [
                    ['factory' => 'fixture1'],
                    ['factory' => 'fixture2'],
                    ['factory' => 'added_fixture'],
                    ['factory' => 'fixture3']
                ],
            ],
        ];
    }

    /**
     * @dataProvider removeFixturesProvider
     *
     * @param array $existingFixtures
     * @param array $config
     * @param array $expectedOrder
     * @return void
     */
    public function testRemoveFixtures(array $existingFixtures, array $config, array $expectedOrder): void
    {
        $fixtures = $this->processApply($existingFixtures, $config);
        $this->assertEquals($expectedOrder, $fixtures);
    }

    /**
     * @return array
     */
    public static function removeFixturesProvider(): array
    {
        return [
            'remove_fixture' => [
                'existingFixtures' => [['factory' => 'fixture'], ['factory' => 'fixture2']],
                'config' => [
                    [
                        'path' => 'fixture',
                        'newPath' => null,
                        'before' => null,
                        'after' => null,
                        'remove' => true,
                    ]
                ],
                'expectedOrder' => [['factory' => 'fixture2']],
            ],
            'remove_one_of_same_fixtures' => [
                'existingFixtures' => [['factory' => 'fixture'], ['factory' => 'fixture'], ['factory' => 'fixture2']],
                'config' => [
                    [
                        'path' => 'fixture',
                        'newPath' => null,
                        'before' => null,
                        'after' => null,
                        'remove' => true,
                    ]
                ],
                'expectedOrder' => [['factory' => 'fixture'], ['factory' => 'fixture2']],
            ],
            'remove_all_of_same_fixtures' => [
                'existingFixtures' => [['factory' => 'fixture'], ['factory' => 'fixture'], ['factory' => 'fixture2']],
                'config' => [
                    [
                        'path' => 'fixture',
                        'newPath' => null,
                        'before' => null,
                        'after' => null,
                        'remove' => true,
                    ],
                    [
                        'path' => 'fixture',
                        'newPath' => null,
                        'before' => null,
                        'after' => null,
                        'remove' => true,
                    ]
                ],
                'expectedOrder' => [['factory' => 'fixture2']],
            ],
        ];
    }

    /**
     * @dataProvider replaceFixturesProvider
     *
     * @param array $existingFixtures
     * @param array $config
     * @param array $expectedOrder
     * @return void
     */
    public function testReplaceFixtures(array $existingFixtures, array $config, array $expectedOrder): void
    {
        $fixtures = $this->processApply($existingFixtures, $config);
        $this->assertEquals($expectedOrder, $fixtures);
    }

    /**
     * @return array
     */
    public static function replaceFixturesProvider(): array
    {
        return [
            'replace_one_fixture' => [
                'existingFixtures' => [['factory' => 'fixture'], ['factory' => 'fixture2']],
                'config' => [
                    [
                        'path' => 'fixture',
                        'newPath' => 'new_fixture',
                        'before' => null,
                        'after' => null,
                        'remove' => false,
                    ]
                ],
                'expectedOrder' => [['factory' => 'new_fixture'], ['factory' => 'fixture2']],
            ],
            'replace_all_fixture' => [
                'existingFixtures' => [['factory' => 'fixture'], ['factory' => 'fixture'], ['factory' => 'fixture2']],
                'config' => [
                    [
                        'path' => 'fixture',
                        'newPath' => 'new_fixture',
                        'before' => null,
                        'after' => null,
                        'remove' => false,
                    ]
                ],
                'expectedOrder' => [
                    ['factory' => 'new_fixture'],
                    ['factory' => 'new_fixture'],
                    ['factory' => 'fixture2']
                ],
            ],
        ];
    }

    /**
     * Process apply configurations
     *
     * @param array $existingFixtures
     * @param array $config
     * @return array
     */
    private function processApply(array $existingFixtures, array $config): array
    {
        $this->setConfig($config);
        $fixtures = $this->object->apply($existingFixtures);

        return array_values($fixtures);
    }

    /**
     * Set config to method scope
     *
     * @param array $config
     * @return void
     */
    private function setConfig(array $config): void
    {
        $this->object->setGlobalConfig([]);
        $this->object->setClassConfig([]);
        $this->object->setDataSetConfig([]);
        $this->object->setMethodConfig($config);
    }
}
